<h4 class="mb-3">تعديل المؤسسة #<?=$tenant['id']?></h4>
<form method="post" class="card card-body">
  <input type="hidden" name="_csrf" value="<?=\Core\Helpers::e($csrf)?>">
  <input type="hidden" name="id" value="<?=$tenant['id']?>">
  <div class="row g-2">
    <div class="col-md-6"><label>الاسم</label><input class="form-control" name="name" value="<?=\Core\Helpers::e($tenant['name'])?>"></div>
    <div class="col-md-6"><label>slug</label><input class="form-control" name="slug" value="<?=\Core\Helpers::e($tenant['slug'])?>"></div>
    <div class="col-md-4"><label>الباقة</label><select class="form-select" name="plan_id"><?php foreach($plans as $p):?><option value="<?=$p['id']?>" <?=$tenant['plan_id']==$p['id']?'selected':''?>><?=$p['name']?></option><?php endforeach;?></select></div>
    <div class="col-md-4"><label>max users</label><input class="form-control" name="max_users" value="<?=$tenant['max_users']??25?>"></div>
    <div class="col-md-4"><label>max storage MB</label><input class="form-control" name="max_storage_mb" value="<?=$tenant['max_storage_mb']??1024?>"></div>
    <div class="col-md-4"><label>max documents</label><input class="form-control" name="max_documents" value="<?=$tenant['max_documents']??50000?>"></div>
    <div class="col-md-4"><label>theme</label><select class="form-select" name="theme_mode"><option value="light" <?=($tenant['theme_mode']??'light')==='light'?'selected':''?>>light</option><option value="dark" <?=($tenant['theme_mode']??'')==='dark'?'selected':''?>>dark</option></select></div>
    <div class="col-md-4"><label>primary color</label><input type="color" name="primary_color" class="form-control form-control-color" value="<?=$tenant['primary_color']??'#0d6efd'?>"></div>
    <div class="col-12 form-check mt-2"><input class="form-check-input" type="checkbox" name="is_active" <?=$tenant['is_active']?'checked':''?>><label class="form-check-label">مفعلة</label></div>
  </div>
  <button class="btn btn-success mt-3">حفظ</button>
</form>
