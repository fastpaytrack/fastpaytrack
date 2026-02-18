<?php use function App\Lib\e; ?>

<div class="aPageHead">
  <div class="aTitle">Edit Product</div>
  <div class="aActions">
    <a class="aBtn aBtnGhost" href="/admin/products">Back</a>
  </div>
</div>

<div class="aGrid2">
  <div class="aCard">
    <div class="aCardTitle">Product Info</div>

    <form method="POST" action="/admin/products/edit" class="aForm">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= e((string)$p['id']) ?>">

      <div class="aField">
        <label>Name</label>
        <input class="aInput" name="name" value="<?= e((string)$p['name']) ?>" required />
      </div>

      <div class="aField">
        <label>Description</label>
        <textarea class="aInput" name="description" rows="4"><?= e((string)($p['description'] ?? '')) ?></textarea>
      </div>

      <div class="aField">
        <label>Image URL</label>
        <input class="aInput" name="image_url" value="<?= e((string)($p['image_url'] ?? '')) ?>" />
      </div>

      <div class="aField">
        <label>Status</label>
        <select class="aInput" name="is_active">
          <option value="1" <?= ((int)$p['is_active']===1?'selected':'') ?>>Active</option>
          <option value="0" <?= ((int)$p['is_active']===0?'selected':'') ?>>Inactive</option>
        </select>
      </div>

      <hr class="aHr">

      <div class="aCardTitle" style="margin:0 0 10px;">Denominations</div>
      <div class="aMuted" style="margin-bottom:12px;">Tambah/edit nominal. Kosongkan baris jika tidak dipakai.</div>

      <?php
        $rows = $denoms ?? [];
        $want = max(6, count($rows) + 2);
      ?>
      <div class="aDenomGrid">
        <?php for($i=0;$i<$want;$i++): 
          $lab = $rows[$i]['label'] ?? '';
          $amt = $rows[$i]['amount_idr'] ?? '';
        ?>
          <div class="aDenomRow">
            <input class="aInput" name="denom_label[]" placeholder="Label (ex: 10K)" value="<?= e((string)$lab) ?>">
            <input class="aInput aMono" name="denom_amount[]" placeholder="Amount IDR (ex: 10000)" value="<?= e((string)$amt) ?>">
          </div>
        <?php endfor; ?>
      </div>

      <button class="aBtn aBtnPrimary" type="submit">Save Changes</button>
    </form>
  </div>

  <div class="aCard">
    <div class="aCardTitle">Preview</div>
    <div class="aRowFlex" style="align-items:flex-start">
      <div class="aThumbLg" style="background-image:url('<?= e((string)($p['image_url'] ?? '')) ?>')"></div>
      <div>
        <div class="aStrong"><?= e((string)$p['name']) ?></div>
        <div class="aMuted"><?= e((string)($p['description'] ?? '')) ?></div>
      </div>
    </div>
  </div>
</div>
