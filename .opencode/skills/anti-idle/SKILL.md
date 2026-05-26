---
name: anti-idle
description: anti-idle, progress update, command macet, timeout, blocked task. Use when the user wants opencode to stay communicative during long analysis, edits, verification, or when commands may hang.
---

# Anti-Idle

Apply this skill whenever the user asks for "anti-idle" behavior or wants more visible progress during execution.

## Primary Rules

- Do not stay silent when work may take noticeable time.
- Send a short progress update before long analysis, substantial edits, or potentially slow verification.
- If a command or investigation appears stuck, has no meaningful output for about 30 seconds, or may be waiting for interactive input, stop waiting when feasible.
- Report the current status, the last action taken, and the next sensible option whenever progress stalls.
- Avoid interactive commands unless the user explicitly asks for them.
- Prefer non-interactive commands with explicit timeouts when possible.
- For verification, start with the smallest targeted check before broader test runs.
- If blocked, explain the blocker briefly and continue with the safest available next step.
- If user input is truly required, ask one focused question instead of waiting silently.

## Progress Update Style

- Keep updates brief and factual.
- State what is being checked or changed.
- Mention why only when it helps the user understand a delay or tradeoff.
- Do not narrate every trivial read or search.

Good examples:

- "Saya cek alur controller dan model dulu supaya bug-nya bisa dipersempit."
- "Saya sedang verifikasi file yang diubah dengan `php -l` sebelum lanjut ke tes yang lebih luas."
- "Command verifikasi tampak macet, jadi saya hentikan di sini dan lanjut dengan pengecekan yang lebih terarah."

## Stalled Command Handling

When a tool run is slow, hangs, or may be interactive:

1. Stop waiting if there is no meaningful progress.
2. Summarize what command was run.
3. State whether it timed out, hung, or appears to need interaction.
4. Continue with a safer alternative if one exists.
5. Ask the user only if the next step requires a decision or missing information.

## Verification Pattern

Prefer this order:

1. Syntax or narrow file-level checks.
2. Focused module or feature verification.
3. Broader project test commands only when useful and unlikely to waste time.

## Non-Goals

- Do not spam the user with constant micro-updates.
- Do not pause work just to narrate obvious actions.
- Do not leave the user without a status update during long-running work.
