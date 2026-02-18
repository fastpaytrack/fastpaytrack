<?php use function App\Lib\e; ?>

<div class="aPageHead">
  <div class="aTitle">Add Product</div>
  <div class="aActions">
    <a class="aBtn aBtnGhost" href="/admin/products">Back</a>
  </div>
</div>

<div class="aGrid2">
  <div class="aCard">
    <div class="aCardTitle">Product Info</div>

    <form method="POST" action="/admin/products/create" class="aForm">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

      <div class="aField">
        <label>Name</label>
        <input class="aInput" name="name" required />
      </div>

      <div class="aField">
        <label>Description</label>
        <textarea class="aInput" name="description" rows="4"></textarea>
      </div>

      <div class="aField">
        <label>Image URL</label>
        <input class="aInput" name="image_url" placeholder="https://..." />
      </div>

      <div class="aField">
        <label>Status</label>
        <select class="aInput" name="is_active">
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>

      <button class="aBtn aBtnPrimary" type="submit">Save Product</button>
    </form>
  </div>

  <div class="aCard">
    <div class="aCardTitle">Notes</div>
    <div class="aMuted">
      Denominations bisa kamu atur setelah product dibuat (menu Edit).
    </div>
  </div>
</div>
