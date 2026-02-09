<h4 class="mb-3">إضافة <?= $type==='inbound'?'وارد':'صادر' ?></h4>
<form method="post" enctype="multipart/form-data" class="card card-body shadow-sm">
<input type="hidden" name="_csrf" value="<?=\Core\Helpers::e($csrf)?>">
<div class="row g-3">
<div class="col-md-4"><label>رقم الوثيقة</label><input name="doc_number" class="form-control" required></div>
<div class="col-md-8"><label>الموضوع</label><input name="subject" class="form-control" required></div>
<div class="col-md-4"><label>التاريخ</label><input type="date" name="<?= $type==='inbound'?'received_date':'sent_date' ?>" class="form-control"></div>
<div class="col-md-4"><label><?= $type==='inbound'?'الجهة المرسلة':'الجهة المستلمة' ?></label><input name="<?= $type==='inbound'?'sender_entity':'receiver_entity' ?>" class="form-control"></div>
<div class="col-md-4"><label>القسم</label><select name="department_id" class="form-select"><option value="">--</option><?php foreach($departments as $dep):?><option value="<?=$dep['id']?>"><?=\Core\Helpers::e($dep['name'])?></option><?php endforeach;?></select></div>
<?php if($type==='outbound'): ?><div class="col-md-6"><label>مرجع وارد</label><select name="inbound_reference_id" class="form-select"><option value="">بدون</option><?php foreach($inboundRefs as $ref):?><option value="<?=$ref['id']?>"><?=\Core\Helpers::e($ref['doc_number'])?> - <?=\Core\Helpers::e($ref['subject'])?></option><?php endforeach;?></select></div><?php endif; ?>
<div class="col-md-4"><label>الأولوية</label><select name="priority" class="form-select"><option>normal</option><option>high</option><option>urgent</option></select></div>
<div class="col-md-4"><label>السرية</label><select name="confidentiality" class="form-select"><option>normal</option><option>confidential</option><option>top_secret</option></select></div>
<div class="col-md-4"><label>الحالة</label><select name="status" class="form-select"><option value="new">new</option><option value="reviewed">reviewed</option><option value="archived">archived</option></select></div>
<div class="col-12"><label>ملاحظات</label><textarea name="notes" class="form-control"></textarea></div>
<div class="col-12"><label>المرفقات</label><input type="file" name="attachments[]" multiple class="form-control"></div>
</div>
<button class="btn btn-success mt-3">حفظ</button>
</form>
