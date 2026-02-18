<?php use function App\Lib\e; use function App\Lib\money_idr; ?>

<div class="aPageHead">
  <div class="aTitle">Products</div>
  <div class="aActions">
    <form class="aSearch" method="GET" action="/admin/products">
      <input class="aInput" name="q" value="<?= e($q ?? '') ?>" placeholder="Search product..." />
    </form>
    <a class="aBtn aBtnPrimary" href="/admin/products/create">Add Product</a>
  </div>
</div>

<div class="aCard">
  <div class="aTableWrap">
    <table class="aTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Product</th>
          <th>Active</th>
          <th>Denominations</th>
          <th style="text-align:right">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($products)): ?>
        <tr><td colspan="5" class="aEmpty">No products.</td></tr>
      <?php else: ?>
        <?php foreach ($products as $p): ?>
          <tr>
            <td class="aMono"><?= e((string)$p['id']) ?></td>
            <td>
              <div class="aRowFlex">
                <div class="aThumb" style="background-image:url('<?= e((string)($p['image_url'] ?? '')) ?>')"></div>
                <div>
                  <div class="aStrong"><?= e((string)$p['name']) ?></div>
                  <div class="aMuted"><?= e((string)($p['description'] ?? '')) ?></div>
                </div>
              </div>
            </td>
            <td>
              <span class="aPill <?= ((int)$p['is_active']===1?'isOn':'isOff') ?>">
                <?= ((int)$p['is_active']===1?'Active':'Inactive') ?>
              </span>
            </td>
            <td class="aMono"><?= e((string)($denomMap[(int)$p['id']] ?? 0)) ?></td>
            <td style="text-align:right">
              <a class="aBtn aBtnGhost" href="/admin/products/edit?id=<?= e((string)$p['id']) ?>">Edit</a>
              <form method="POST" action="/admin/products/delete" style="display:inline-block" onsubmit="return confirm('Delete product ini?')">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="id" value="<?= e((string)$p['id']) ?>">
                <button class="aBtn aBtnDanger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
