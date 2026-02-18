<?php
$p = $p ?? null;
$mode = $mode ?? 'create';
$isEdit = $mode === 'edit';
?>
<div class="topbar">
  <div class="brand">
    <div class="brandTitle"><?= $isEdit ? 'Edit Product' : 'Add Product' ?></div>
    <div class="brandSub">Kelola data produk</div>
  </div>
</div>

<div class="panel">
  <div class="panelHead"><?= $isEdit ? 'Update Produk' : 'Tambah Produk' ?></div>
  <div class="panelBody">
    <form method="POST" action="<?= $isEdit ? '/admin/products/edit' : '/admin/products/create' ?>">
      <input type="hidden" name="_csrf" value="<?= ae($csrf) ?>">
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)($p['id'] ?? 0) ?>">
      <?php endif; ?>

      <div class="field">
        <label>Nama Produk</label>
        <input name="name" value="<?= ae((string)($p['name'] ?? '')) ?>" required>
      </div>

      <div class="field">
        <label>Deskripsi</label>
        <textarea name="description"><?= ae((string)($p['description'] ?? '')) ?></textarea>
      </div>

      <div class="formRow">
        <div class="field">
          <label>Harga (IDR)</label>
          <input name="price_idr" value="<?= ae((string)($p['price_idr'] ?? '')) ?>" placeholder="50000" required>
        </div>
        <div class="field">
          <label>Stock</label>
          <input name="stock" value="<?= ae((string)($p['stock'] ?? '0')) ?>" placeholder="10">
        </div>
      </div>

      <div class="field">
        <label>Foto Produk (URL)</label>
        <input name="image_url" value="<?= ae((string)($p['image_url'] ?? '')) ?>" placeholder="https://...">
      </div>

      <div style="display:flex;gap:10px;margin-top:12px;">
        <button class="btn btnBlue" type="submit" style="height:44px;"><?= $isEdit ? 'Save' : 'Create' ?></button>
        <a class="btn btnGhost" href="/admin/products" style="height:44px;display:inline-flex;align-items:center;">Back</a>
      </div>
    </form>
  </div>
</div>
