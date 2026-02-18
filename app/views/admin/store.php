<?php use function App\Lib\e; ?>

<div class="aPageHead">
  <div class="aTitle">Manage Store</div>
</div>

<div class="aCard">
  <div class="aCardTitle">Dashboard Ads</div>

  <form method="POST" action="/admin/store" class="aForm" style="max-width:860px">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <?php
      $first = $ads[0] ?? ['slot'=>'dashboard_banner_1','image_url'=>'','target_url'=>'','is_active'=>0];
    ?>
    <div class="aField">
      <label>Slot</label>
      <select class="aInput" name="slot">
        <option value="dashboard_banner_1" <?= ((string)$first['slot']==='dashboard_banner_1'?'selected':'') ?>>dashboard_banner_1</option>
      </select>
    </div>

    <div class="aField">
      <label>Image URL</label>
      <input class="aInput" name="image_url" value="<?= e((string)$first['image_url']) ?>" placeholder="https://...">
    </div>

    <div class="aField">
      <label>Target URL</label>
      <input class="aInput" name="target_url" value="<?= e((string)$first['target_url']) ?>" placeholder="https://...">
    </div>

    <div class="aField">
      <label>Active</label>
      <select class="aInput" name="is_active">
        <option value="1" <?= ((int)$first['is_active']===1?'selected':'') ?>>Yes</option>
        <option value="0" <?= ((int)$first['is_active']===0?'selected':'') ?>>No</option>
      </select>
    </div>

    <button class="aBtn aBtnPrimary" type="submit">Save</button>
  </form>
</div>
