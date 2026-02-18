<div class="topbar">
  <div class="brand">
    <div class="brandTitle">Manage Store</div>
    <div class="brandSub">Ganti gambar/link iklan dashboard</div>
  </div>
</div>

<div class="panel">
  <div class="panelHead">Ads Slots</div>
  <div class="panelBody">
    <?php foreach (($rows ?? []) as $r): ?>
      <form method="POST" action="/admin/store/save" style="border:1px solid #eef2f7;border-radius:14px;padding:12px;margin-bottom:12px;">
        <input type="hidden" name="_csrf" value="<?= ae($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <div style="font-weight:800;margin-bottom:10px;">Slot: <?= ae((string)$r['slot']) ?></div>

        <div class="field">
          <label>Image URL</label>
          <input name="image_url" value="<?= ae((string)$r['image_url']) ?>">
        </div>

        <div class="field">
          <label>Target URL</label>
          <input name="target_url" value="<?= ae((string)$r['target_url']) ?>">
        </div>

        <div class="field">
          <label>Active</label>
          <select name="is_active">
            <option value="0" <?= ((int)$r['is_active']===0)?'selected':'' ?>>OFF</option>
            <option value="1" <?= ((int)$r['is_active']===1)?'selected':'' ?>>ON</option>
          </select>
        </div>

        <button class="btn btnBlue" type="submit" style="height:44px;">Save</button>
      </form>
    <?php endforeach; ?>
  </div>
</div>
