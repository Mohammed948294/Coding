<h4 class="mb-3">تعديل الوثيقة #<?=$document['id']?></h4>
<form method="post" class="card card-body shadow-sm">
  <input type="hidden" name="_csrf" value="<?=\Core\Helpers::e($csrf)?>">
  <div class="row g-3">
    <div class="col-md-8"><label>الموضوع</label><input class="form-control" name="subject" value="<?=\Core\Helpers::e($document['subject'])?>" required></div>
    <div class="col-md-4"><label>الحالة</label><select class="form-select" name="status"><?php foreach(['new','reviewed','archived'] as $st):?><option <?=$document['status']===$st?'selected':''?>><?=$st?></option><?php endforeach;?></select></div>
    <div class="col-md-4"><label>الأولوية</label><select class="form-select" name="priority"><?php foreach(['normal','high','urgent'] as $p):?><option <?=$document['priority']===$p?'selected':''?>><?=$p?></option><?php endforeach;?></select></div>
    <div class="col-md-4"><label>السرية</label><select class="form-select" name="confidentiality"><?php foreach(['normal','confidential','top_secret'] as $c):?><option <?=$document['confidentiality']===$c?'selected':''?>><?=$c?></option><?php endforeach;?></select></div>
    <div class="col-12"><label>ملاحظات</label><textarea class="form-control" name="notes"><?=\Core\Helpers::e($document['notes'])?></textarea></div>
  </div>
  <button class="btn btn-primary mt-3">تحديث</button>
</form>
