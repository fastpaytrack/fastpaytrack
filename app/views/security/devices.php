<?php
/** @var array $devices */
/** @var string $csrf */
/** @var int $notify_login_email */
?>
<div class="container" style="max-width: 760px; margin: 0 auto; padding: 18px;">
  <div style="background: rgba(255,255,255,.12); border-radius: 22px; padding: 18px; color: #fff;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
      <div>
        <div style="font-size:26px; font-weight:800; line-height:1.1;">Security</div>
        <div style="opacity:.9; margin-top:4px;">Manage Devices / Riwayat Login</div>
      </div>
      <a href="/settings" style="text-decoration:none;">
        <button type="button" style="border:0; padding:10px 14px; border-radius:12px; font-weight:700; cursor:pointer;">
          Kembali
        </button>
      </a>
    </div>
  </div>

  <div
    style="background:#fff; border-radius: 22px; padding: 16px; margin-top: 14px; box-shadow: 0 10px 30px rgba(0,0,0,.08);">

    <!-- Toggle Notifikasi -->
    <div style="border:1px solid #e5e7eb; border-radius:16px; padding:14px; margin-bottom:14px;">
      <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <div>
          <div style="font-weight:800; font-size:16px;">Notifikasi Login via Email</div>
          <div style="color:#6b7280; margin-top:4px; font-size:13px;">
            Jika ada login dari device/IP baru, sistem akan mengirim email pemberitahuan.
          </div>
        </div>

        <form method="POST" action="/settings/security/notify-email" style="margin:0;">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="notify_login_email" value="<?= $notify_login_email ? 0 : 1 ?>">
          <button type="submit" style="
              border:0;
              padding:10px 14px;
              border-radius:14px;
              font-weight:800;
              cursor:pointer;
              min-width:140px;
              <?= $notify_login_email ? 'background:#4f46e5; color:#fff;' : 'background:#e5e7eb; color:#111827;' ?>
            ">
            <?= $notify_login_email ? 'ON' : 'OFF' ?>
          </button>
        </form>
      </div>
    </div>

    <!-- Action buttons -->
    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px;">
      <a href="/settings" style="flex:1; text-decoration:none;">
        <button type="button"
          style="width:100%; border:1px solid #e5e7eb; background:#fff; padding:12px; border-radius:14px; font-weight:800; cursor:pointer;">
          Kembali
        </button>
      </a>

      <a href="/settings/security/revoke-others" style="flex:1; text-decoration:none;">
        <button type="button"
          style="width:100%; border:0; background:#4f46e5; color:#fff; padding:12px; border-radius:14px; font-weight:800; cursor:pointer;">
          Logout Device Lain
        </button>
      </a>
    </div>

    <!-- Devices list -->
    <?php if (empty($devices)): ?>
      <div style="padding:14px; border:1px dashed #e5e7eb; border-radius:14px; color:#6b7280;">
        Belum ada riwayat device.
      </div>
    <?php else: ?>
      <?php foreach ($devices as $d): ?>
        <?php
        $isCurrent = !empty($d['is_current']);
        $revoked = !empty($d['revoked_at']);
        $title = trim((string) ($d['device_label'] ?? 'Unknown Device'));
        $ip = (string) ($d['ip_address'] ?? '-');
        $loginAt = (string) ($d['created_at'] ?? '-');
        $seenAt = (string) ($d['last_seen_at'] ?? '-');
        $ua = (string) ($d['user_agent'] ?? '');
        ?>

        <div style="border:1px solid #e5e7eb; border-radius:18px; padding:14px; margin-bottom:12px;">
          <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div style="min-width: 220px;">
              <div style="font-weight:600; font-size:16px; margin-bottom:6px;">
                <?= htmlspecialchars($title) ?>
                <?php if ($isCurrent): ?>
                  <span
                    style="margin-left:8px; font-size:12px; font-weight:600; padding:4px 10px; border-radius:999px; background:#eef2ff; color:#3730a3;">
                    Device ini
                  </span>
                <?php endif; ?>
                <?php if ($revoked): ?>
                  <span
                    style="margin-left:8px; font-size:12px; font-weight:600; padding:4px 10px; border-radius:999px; background:#f3f4f6; color:#6b7280;">
                    Logged out
                  </span>
                <?php endif; ?>
              </div>

              <div style="color:#374151; font-size:13px; line-height:1.6;">
                <div><b>IP:</b> <?= htmlspecialchars($ip) ?></div>
                <div><b>Login:</b> <?= htmlspecialchars($loginAt) ?></div>
                <div><b>Last seen:</b> <?= htmlspecialchars($seenAt) ?></div>
              </div>
            </div>

            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
              <button type="button" onclick="toggleUa(<?= (int) $d['id'] ?>)"
                style="border:1px solid #e5e7eb; background:#fff; padding:10px 12px; border-radius:14px; font-weight:800; cursor:pointer;">
                Lihat Detail Device
              </button>

              <?php if (!$isCurrent && !$revoked): ?>
                <a href="/settings/security/revoke/<?= (int) $d['id'] ?>" style="text-decoration:none;">
                  <button type="button"
                    style="border:0; background:#111827; color:#fff; padding:10px 12px; border-radius:14px; font-weight:800; cursor:pointer;">
                    Logout device
                  </button>
                </a>
              <?php elseif ($isCurrent): ?>
                <button type="button" disabled
                  style="border:0; background:#e5e7eb; color:#6b7280; padding:10px 12px; border-radius:14px; font-weight:800;">
                  Aktif
                </button>
              <?php else: ?>
                <button type="button" disabled
                  style="border:0; background:#e5e7eb; color:#6b7280; padding:10px 12px; border-radius:14px; font-weight:800;">
                  Selesai
                </button>
              <?php endif; ?>
            </div>
          </div>

          <div id="ua-<?= (int) $d['id'] ?>" style="display:none; margin-top:12px;">
            <div style="font-size:13px; font-weight:600; margin-bottom:6px;">User-Agent</div>
            <div
              style="border:1px solid #e5e7eb; border-radius:14px; padding:12px; background:#fafafa; color:#374151; word-break:break-word;">
              <?= htmlspecialchars($ua ?: '-') ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</div>

<script>
  function toggleUa(id) {
    var el = document.getElementById('ua-' + id);
    if (!el) return;
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
  }
</script>