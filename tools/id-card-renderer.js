#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
const JSZip = require('jszip');

function arg(name, fallback = '') {
  const idx = process.argv.indexOf(`--${name}`);
  return idx >= 0 && process.argv[idx + 1] ? process.argv[idx + 1] : fallback;
}

function writeProgress(progressFile, payload) {
  if (!progressFile) return;
  const tmp = `${progressFile}.tmp`;
  fs.mkdirSync(path.dirname(progressFile), { recursive: true });
  fs.writeFileSync(tmp, JSON.stringify({ updated_at: new Date().toISOString(), ...payload }, null, 2));
  fs.renameSync(tmp, progressFile);
}

function renderCliProgress(payload) {
  const total = Number(payload.total || 0);
  const processed = Number(payload.processed || 0);
  const pct = total > 0 ? Math.min(100, Math.round((processed / total) * 100)) : 0;
  const terminalWidth = process.stdout.columns || 120;
  const width = Math.max(16, Math.min(34, terminalWidth - 86));
  const filled = Math.round((pct / 100) * width);
  const bar = `${'█'.repeat(filled)}${'░'.repeat(width - filled)}`;
  const stageMax = Math.max(18, terminalWidth - width - 46);
  const stage = String(payload.stage || payload.status || '').replace(/\s+/g, ' ').slice(0, stageMax);
  const current = payload.current ? ` | ${String(payload.current).slice(0, 24)}` : '';
  const line = `[${bar}] ${String(pct).padStart(3, ' ')}% ${processed}/${total || '?'} | ${stage}${current}`;

  if (process.stdout.isTTY) {
    if (process.stdout.clearLine) process.stdout.clearLine(0);
    if (process.stdout.cursorTo) process.stdout.cursorTo(0);
    process.stdout.write(line.slice(0, Math.max(1, terminalWidth - 1)));
    if (payload.status === 'done' || payload.status === 'failed') {
      process.stdout.write('\n');
    }
    return;
  }

  if (payload.status === 'done' || payload.status === 'failed') {
    console.log(line);
  }
}

function reportProgress(progressFile, payload) {
  writeProgress(progressFile, payload);
  renderCliProgress(payload);
}

async function waitForAssets(page, timeoutMs) {
  await page.evaluate(async (timeout) => {
    const withTimeout = (promise) => Promise.race([
      promise,
      new Promise((resolve) => setTimeout(resolve, timeout)),
    ]);

    if (document.fonts && document.fonts.ready) {
      await withTimeout(document.fonts.ready.catch(() => {}));
    }

    await withTimeout(Promise.all(Array.from(document.images || []).map((img) => {
      if (img.complete) return Promise.resolve();
      if (img.decode) return img.decode().catch(() => {});
      return new Promise((resolve) => {
        img.onload = resolve;
        img.onerror = resolve;
      });
    })));
  }, timeoutMs);
}

async function neutralizeFontWait(page) {
  await page.addStyleTag({ content: '*{font-display:swap!important;}' }).catch(() => {});
  await page.evaluate(() => {
    if (!document.fonts) return;
    const ready = Promise.resolve(document.fonts);
    try {
      Object.defineProperty(document.fonts, 'ready', { configurable: true, get: () => ready });
    } catch (e) {}
  }).catch(() => {});
}

async function screenshotElementByClip(page, element, timeoutMs) {
  const handle = await element.evaluateHandle((source) => {
    const cloneId = '__id_card_render_clone__';
    const old = document.getElementById(cloneId);
    if (old) old.remove();

    const clone = source.cloneNode(true);
    clone.id = cloneId;
    clone.classList.add('__id-card-render-clone');
    Object.assign(clone.style, {
      position: 'fixed',
      left: '0px',
      top: '0px',
      margin: '0',
      zIndex: '2147483647',
      display: 'block',
      visibility: 'visible',
      transform: 'none',
      opacity: '1',
    });

    document.body.appendChild(clone);
    return clone;
  });

  const clone = handle.asElement();
  if (!clone) {
    throw new Error('Clone kartu tidak bisa dibuat.');
  }

  await page.waitForTimeout(50);
  const box = await clone.boundingBox();
  if (!box || box.width <= 0 || box.height <= 0) {
    await clone.evaluate((el) => el.remove()).catch(() => {});
    throw new Error('Bounding box kartu tidak ditemukan.');
  }

  const previousViewport = page.viewportSize() || { width: 1200, height: 1700 };
  const neededWidth = Math.max(previousViewport.width, Math.ceil(box.width) + 20);
  const neededHeight = Math.max(previousViewport.height, Math.ceil(box.height) + 20);
  if (neededWidth !== previousViewport.width || neededHeight !== previousViewport.height) {
    await page.setViewportSize({ width: neededWidth, height: neededHeight });
    await page.waitForTimeout(50);
  }

  const freshBox = await clone.boundingBox();
  if (!freshBox || freshBox.width <= 0 || freshBox.height <= 0) {
    await clone.evaluate((el) => el.remove()).catch(() => {});
    throw new Error('Bounding box kartu tidak ditemukan setelah clone.');
  }

  const png = await page.screenshot({
    type: 'png',
    timeout: timeoutMs,
    clip: {
      x: Math.max(0, Math.floor(freshBox.x)),
      y: Math.max(0, Math.floor(freshBox.y)),
      width: Math.ceil(freshBox.width),
      height: Math.ceil(freshBox.height),
    },
  });

  await clone.evaluate((el) => el.remove()).catch(() => {});
  return png;
}

async function main() {
  const input = arg('input');
  const output = arg('output');
  const progressFile = arg('progress-file');
  const scale = Math.max(1, parseInt(arg('scale', '3'), 10) || 3);
  const chunkSize = Math.max(1, parseInt(arg('chunk-size', '50'), 10) || 50);
  const loadTimeout = Math.max(5000, parseInt(arg('load-timeout', '120000'), 10) || 120000);
  const assetTimeout = Math.max(1000, parseInt(arg('asset-timeout', '15000'), 10) || 15000);

  if (!input || !output) {
    throw new Error('Usage: node tools/id-card-renderer.js --input file.html --output output-dir --scale 3 --chunk-size 50 --progress-file progress.json');
  }

  fs.mkdirSync(output, { recursive: true });
  reportProgress(progressFile, { status: 'starting', stage: 'Membuka Chromium', total: 0, processed: 0, zip_files: [] });

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 1200, height: 1700 },
    deviceScaleFactor: scale,
  });
  page.setDefaultTimeout(loadTimeout);
  page.setDefaultNavigationTimeout(loadTimeout);

  reportProgress(progressFile, { status: 'loading', stage: 'Memuat HTML ID Card', total: 0, processed: 0, zip_files: [] });
  await page.goto(`file://${path.resolve(input)}`, { waitUntil: 'domcontentloaded', timeout: loadTimeout });
  await page.waitForSelector('.kartu-id-export', { timeout: loadTimeout });

  reportProgress(progressFile, { status: 'loading', stage: 'Memuat font dan gambar', total: 0, processed: 0, zip_files: [] });
  await waitForAssets(page, assetTimeout);
  await neutralizeFontWait(page);
  await page.waitForTimeout(300);

  const cards = await page.$$('.kartu-id-export');
  let part = 1;
  let zip = new JSZip();
  let countInPart = 0;
  let processed = 0;
  const zipFiles = [];
  const totalParts = Math.max(1, Math.ceil(cards.length / chunkSize));

  if (cards.length === 0) {
    throw new Error('Tidak ada elemen .kartu-id-export di HTML input.');
  }

  reportProgress(progressFile, { status: 'rendering', stage: 'Mulai render kartu', total: cards.length, processed, total_parts: totalParts, current_part: part, zip_files: zipFiles });

  for (let i = 0; i < cards.length; i++) {
    const card = cards[i];
    const filename = await card.evaluate((el, idx) => el.dataset.filename || `kartu_${idx + 1}`, i);
    reportProgress(progressFile, { status: 'rendering', stage: `Render ${filename}`, total: cards.length, processed, total_parts: totalParts, current_part: part, zip_files: zipFiles, current: filename });
    const png = await screenshotElementByClip(page, card, loadTimeout);
    zip.file(`${filename}.png`, png);
    countInPart++;
    processed++;

    if (countInPart >= chunkSize || i === cards.length - 1) {
      const paddedPart = String(part).padStart(2, '0');
      const paddedTotal = String(totalParts).padStart(2, '0');
      const zipName = totalParts === 1
        ? 'id-card-batch.zip'
        : `id-card-batch-part-${paddedPart}-of-${paddedTotal}.zip`;
      const zipPath = path.join(output, zipName);
      reportProgress(progressFile, { status: 'zipping', stage: `Membuat ZIP part ${part}/${totalParts}`, total: cards.length, processed, total_parts: totalParts, current_part: part, zip_files: zipFiles });
      const buffer = await zip.generateAsync({ type: 'nodebuffer', compression: 'DEFLATE', compressionOptions: { level: 1 } });
      fs.writeFileSync(zipPath, buffer);
      zipFiles.push(zipName);
      reportProgress(progressFile, { status: 'rendering', stage: `ZIP part ${part}/${totalParts} tersimpan`, total: cards.length, processed, total_parts: totalParts, current_part: part, zip_files: zipFiles });
      zip = new JSZip();
      countInPart = 0;
      part++;
    }
  }

  await browser.close();
  reportProgress(progressFile, { status: 'done', stage: 'Selesai', total: cards.length, processed, total_parts: totalParts, current_part: totalParts, zip_files: zipFiles, output_dir: output });
  if (!process.stdout.isTTY) {
    console.log(`done ${processed} cards`);
  } else {
    console.log(`done ${processed} cards`);
  }
}

main().catch((err) => {
  const progressFile = arg('progress-file');
  reportProgress(progressFile, { status: 'failed', stage: 'Gagal', error: err && err.stack ? err.stack : String(err) });
  console.error(err && err.stack ? err.stack : err);
  process.exit(1);
});







