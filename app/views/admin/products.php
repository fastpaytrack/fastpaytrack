<?php use App\Lib\AdminUtil; $money=$money ?? fn(int $n)=>AdminUtil::moneyIdr($n); ?>
<div class="topbar">
  <div class="brand">
    <div class="brandTitle">Products</div>
    <div class="brandSub">Tambah/Edit/Hapus produk</div>
  </div>
  <div class="topActions">
    <a class="btn btnBlue" href="/admin/products/create">+ Add Product</a>
  </div>
</div>

<div class="panel">
  <div class="panelHead">Daftar Produk</div>
  <div class="panelBody" style="padding:0;">
    <table>
      <thead>
      <tr>
        <th>ID</th>
        <th>Foto</th>
        <th>Nama</th>
        <th>Harga</th>
        <th>Stock</th>
        <th>Action</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach (($rows ?? []) as $p): ?>
        <tr>
          <td><?= (int)$p['id'] ?></td>
          <td>
            <?php if (!empty($p['image_url'])): ?>
              <img src="<?= ae((string)$p['image_url']) ?>" style="width:48px;height:32px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;">
            <?php else: ?>—<?php endif; ?>
          </td>
          <td><?= ae((string)$p['name']) ?></td>
          <td><?= ae($money((int)($p['price_idr'] ?? 0))) ?></td>
          <td><?= ae((string)($p['stock'] ?? 0)) ?></td>
          <td style="display:flex;gap:8px;align-items:center;">
            <a class="btn btnGhost" href="/admin/products/edit?id=<?= (int)$p['id'] ?>">Edit</a>
            <form method="POST" action="/admin/products/delete" onsubmit="return confirm('Hapus produk ini?');" style="margin:0;">
              <input type="hidden" name="_csrf" value="<?= ae($csrf) ?>">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button class="btn" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
