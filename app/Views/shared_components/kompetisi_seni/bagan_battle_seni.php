<style>
    .dps-battle-bracket-fallback{display:flex;gap:18px;align-items:flex-start;min-width:max-content}.dps-battle-round{min-width:280px}.dps-battle-title{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#64748b;margin-bottom:10px}.dps-battle-match{border:1px solid #e2e8f0;border-radius:12px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.06);margin-bottom:14px;overflow:hidden}.dps-battle-team{display:flex;justify-content:space-between;gap:12px;padding:10px 12px}.dps-battle-team+.dps-battle-team{border-top:1px solid #f1f5f9}.dps-battle-name{font-size:13px;font-weight:700;color:#0f172a;margin:0}.dps-battle-kontingen{font-size:11px;font-weight:600;color:#64748b;margin:2px 0 0}.dps-battle-empty{font-size:13px;font-weight:600;color:#94a3b8;font-style:italic}.battle-card{display:flex;align-items:center;height:100%;padding:4px 8px;box-sizing:border-box}.battle-info{overflow:hidden}.battle-name{font-size:13px;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.2;margin:0}.battle-kontingen{font-size:11px;font-weight:500;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:2px 0 0}
</style>
<?php
$baganData = json_decode((string) ($kompetisi_seni->bagan_battle_seni ?? ''), true);
$fallbackTeams = is_array($baganData['teams'] ?? null) ? $baganData['teams'] : [];
?>
<div class="row h-100">
    <div class="col-12 overflow-scroll">
        <?php if ($fallbackTeams !== []) : ?>
            <div id="baganBattleFallback<?= $kompetisi_seni->id_kompetisi_seni ?>" class="dps-battle-bracket-fallback mb-4">
                <div class="dps-battle-round">
                    <div class="dps-battle-title">Babak Awal</div>
                    <?php foreach ($fallbackTeams as $matchIndex => $teams) : ?>
                        <div class="dps-battle-match">
                            <?php foreach ([0, 1] as $teamIndex) : ?>
                                <?php $team = $teams[$teamIndex] ?? null; ?>
                                <div class="dps-battle-team">
                                    <div>
                                        <p class="dps-battle-name"><?= esc((string) ($team['anggota_kelompok_peserta_seni'] ?? 'BYE')) ?></p>
                                        <p class="dps-battle-kontingen"><?= esc((string) ($team['nama_kontingen'] ?? '-')) ?></p>
                                    </div>
                                    <span class="dps-battle-empty">#<?= esc((string) ($team['nomor_slot'] ?? (($matchIndex * 2) + $teamIndex + 1))) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <div id="baganBattleSeni<?= $kompetisi_seni->id_kompetisi_seni ?>" class="PERSILAT"></div>
    </div>
</div>
<script>
    (function(){
        let matchData<?= $kompetisi_seni->id_kompetisi_seni ?> = <?= $kompetisi_seni->bagan_battle_seni ?>;
        let params<?= $kompetisi_seni->id_kompetisi_seni ?> = {teamWidth:260,scoreWidth:35,matchMargin:60,roundMargin:60,init:matchData<?= $kompetisi_seni->id_kompetisi_seni ?>,disableToolbar:true,decorator:{edit:function(){},render:function(container,data,score,state){if(state==='empty-bye'){container.append('<div class="dps-battle-empty">BYE</div>');return}if(state==='empty-tbd'){container.append('<div class="dps-battle-empty">TBD</div>');return}if(['entry-no-score','entry-default-win','entry-complete'].indexOf(state)!==-1){container.append('<div class="battle-card"><div class="battle-info"><p class="battle-name">'+(data.anggota_kelompok_peserta_seni||'-')+'</p><p class="battle-kontingen">'+String(data.nama_kontingen||'-').toUpperCase()+'</p></div></div>')}}}};
        params<?= $kompetisi_seni->id_kompetisi_seni ?>.skipConsolationRound = <?= (int) ($kompetisi_seni->juara_tiga_bersama ?? 1) ?> === 1;
        function init(){if(!window.jQuery||!jQuery.fn.bracket){window.setTimeout(init,50);return}jQuery('#baganBattleSeni<?= $kompetisi_seni->id_kompetisi_seni ?>').bracket(params<?= $kompetisi_seni->id_kompetisi_seni ?>);if(jQuery('#baganBattleSeni<?= $kompetisi_seni->id_kompetisi_seni ?> .jQBracket').length>0){jQuery('#baganBattleFallback<?= $kompetisi_seni->id_kompetisi_seni ?>').hide()}}
        if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',init)}else{init()}
    })();
</script>
