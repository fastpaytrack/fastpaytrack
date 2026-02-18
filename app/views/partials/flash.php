<?php
declare(strict_types=1);

/**
 * FLASH UI (Auto dismiss)
 * Support beberapa format session flash yang umum:
 * - $_SESSION['flash'] = ['success' => '...', 'error' => '...']
 * - $_SESSION['_flash'] = ['success' => ['...','...'], 'error' => ['...']]
 * - $_SESSION['flash'] = [['type'=>'success','message'=>'...'], ...]
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
  @session_start();
}

$raw = $_SESSION['flash'] ?? ($_SESSION['_flash'] ?? null);

$items = [];

/** Normalisasi jadi: [ ['type'=>'success','message'=>'...'], ... ] */
if (is_array($raw)) {
  // Case A: ['success'=>'msg', 'error'=>'msg']
  $isAssocTypeToMsg = true;
  foreach ($raw as $k => $v) {
    if (!is_string($k)) {
      $isAssocTypeToMsg = false;
      break;
    }
  }

  if ($isAssocTypeToMsg) {
    foreach ($raw as $type => $val) {
      if (is_array($val)) {
        foreach ($val as $msg) {
          if ($msg !== '' && $msg !== null)
            $items[] = ['type' => (string) $type, 'message' => (string) $msg];
        }
      } else {
        if ($val !== '' && $val !== null)
          $items[] = ['type' => (string) $type, 'message' => (string) $val];
      }
    }
  } else {
    // Case B: [ ['type'=>'success','message'=>'...'], ... ]
    foreach ($raw as $row) {
      if (is_array($row) && isset($row['type'], $row['message'])) {
        $items[] = ['type' => (string) $row['type'], 'message' => (string) $row['message']];
      }
    }
  }
}

// Hapus flash setelah ditampilkan
unset($_SESSION['flash'], $_SESSION['_flash']);

if (!$items)
  return;

function flash_ui_icon(string $type): string
{
  $t = strtolower($type);
  if ($t === 'success')
    return '✓';
  if ($t === 'error' || $t === 'danger')
    return '!';
  if ($t === 'warning')
    return '⚠';
  return 'ℹ';
}

function flash_ui_title(string $type): string
{
  $t = strtolower($type);
  if ($t === 'success')
    return 'Sukses';
  if ($t === 'error' || $t === 'danger')
    return 'Gagal';
  if ($t === 'warning')
    return 'Peringatan';
  return 'Info';
}

?>
<style>
  .flash-stack {
    position: fixed;
    top: 14px;
    left: 50%;
    transform: translateX(-50%);
    width: min(680px, calc(100vw - 24px));
    z-index: 9999;
    pointer-events: none;
  }

  .flash {
    pointer-events: auto;
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 10px 0;
    padding: 14px 14px;
    border-radius: 14px;
    backdrop-filter: blur(10px);
    box-shadow: 0 12px 35px rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .35);
    background: rgba(255, 255, 255, .88);
    color: #0f172a;
    animation: flashIn .22s ease-out;
    overflow: hidden;
    position: relative;
  }

  .flash .icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #fff;
    flex: 0 0 auto;
  }

  .flash .content {
    flex: 1 1 auto;
    min-width: 0;
  }

  .flash .title {
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 2px;
    font-size: 14px;
  }

  .flash .msg {
    font-size: 14px;
    line-height: 1.35;
    opacity: .92;
    word-break: break-word;
  }

  .flash .close {
    appearance: none;
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 8px 10px;
    border-radius: 12px;
    font-size: 18px;
    line-height: 1;
    opacity: .55;
    transition: .15s ease;
    flex: 0 0 auto;
  }

  .flash .close:hover {
    opacity: .9;
    background: rgba(15, 23, 42, .08);
  }

  .flash .bar {
    position: absolute;
    left: 0;
    bottom: 0;
    height: 3px;
    width: 100%;
    opacity: .9;
    transform-origin: left;
  }

  .flash.success .icon {
    background: linear-gradient(135deg, #22c55e, #16a34a);
  }

  .flash.success .bar {
    background: linear-gradient(90deg, #22c55e, #16a34a);
  }

  .flash.error .icon,
  .flash.danger .icon {
    background: linear-gradient(135deg, #ef4444, #dc2626);
  }

  .flash.error .bar,
  .flash.danger .bar {
    background: linear-gradient(90deg, #ef4444, #dc2626);
  }

  .flash.warning .icon {
    background: linear-gradient(135deg, #f59e0b, #d97706);
  }

  .flash.warning .bar {
    background: linear-gradient(90deg, #f59e0b, #d97706);
  }

  .flash.info .icon {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
  }

  .flash.info .bar {
    background: linear-gradient(90deg, #3b82f6, #2563eb);
  }

  .flash.hide {
    animation: flashOut .22s ease-in forwards;
  }

  @keyframes flashIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes flashOut {
    from {
      opacity: 1;
      transform: translateY(0);
    }

    to {
      opacity: 0;
      transform: translateY(-10px);
    }
  }

  @media (max-width: 480px) {
    .flash {
      padding: 12px 12px;
      border-radius: 16px;
    }

    .flash .icon {
      width: 36px;
      height: 36px;
      border-radius: 12px;
    }

    .flash .title,
    .flash .msg {
      font-size: 13px;
    }
  }
</style>

<div class="flash-stack" id="flashStack">
  <?php foreach ($items as $i => $it):
    $type = strtolower(trim((string) ($it['type'] ?? 'info')));
    if (!in_array($type, ['success', 'error', 'danger', 'warning', 'info'], true))
      $type = 'info';
    $msg = (string) ($it['message'] ?? '');
    $id = 'flash_' . $i . '_' . substr(md5($type . $msg . (string) microtime(true)), 0, 8);
    ?>
    <div class="flash <?= htmlspecialchars($type) ?>" id="<?= htmlspecialchars($id) ?>" data-timeout="3200" role="status"
      aria-live="polite">
      <div class="icon"><?= htmlspecialchars(flash_ui_icon($type)) ?></div>
      <div class="content">
        <div class="title"><?= htmlspecialchars(flash_ui_title($type)) ?></div>
        <div class="msg"><?= htmlspecialchars($msg) ?></div>
      </div>
      <button class="close" type="button" aria-label="Tutup"
        onclick="window.__flashClose('<?= htmlspecialchars($id) ?>')">×</button>
      <div class="bar" data-bar></div>
    </div>
  <?php endforeach; ?>
</div>

<script>
  (function () {
    function closeFlash(el) {
      if (!el) return;
      el.classList.add('hide');
      setTimeout(function () {
        if (el && el.parentNode) el.parentNode.removeChild(el);
      }, 260);
    }

    window.__flashClose = function (id) {
      var el = document.getElementById(id);
      closeFlash(el);
    };

    // Auto-dismiss + progress bar
    var list = document.querySelectorAll('#flashStack .flash');
    list.forEach(function (el) {
      var ms = parseInt(el.getAttribute('data-timeout') || '3200', 10);
      var bar = el.querySelector('[data-bar]');
      if (bar) {
        bar.animate([{ transform: 'scaleX(1)' }, { transform: 'scaleX(0)' }], { duration: ms, easing: 'linear', fill: 'forwards' });
      }
      setTimeout(function () { closeFlash(el); }, ms);
    });
  })();
</script>