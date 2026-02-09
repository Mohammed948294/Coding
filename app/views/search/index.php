<h4>البحث</h4>
<form class="row g-2 mb-3 shadow-sm p-2 bg-white rounded">
  <input type="hidden" name="r" value="search">
  <div class="col-md-2"><input class="form-control" name="doc_number" placeholder="رقم" value="<?=\Core\Helpers::e($filters['doc_number']??'')?>"></div>
  <div class="col-md-3"><input class="form-control" name="subject" placeholder="موضوع" value="<?=\Core\Helpers::e($filters['subject']??'')?>"></div>
  <div class="col-md-2"><select class="form-select" name="type"><option value="">النوع</option><option value="inbound">inbound</option><option value="outbound">outbound</option></select></div>
  <div class="col-md-2"><input class="form-control" name="status" placeholder="حالة" value="<?=\Core\Helpers::e($filters['status']??'')?>"></div>
  <div class="col-md-3"><button class="btn btn-primary">بحث</button></div>
</form>
<table class="table bg-white shadow-sm"><tr><th>#</th><th>النوع</th><th>الرقم</th><th>الموضوع</th><th>الحالة</th></tr><?php foreach($results as $r):?><tr><td><?=$r['id']?></td><td><?=$r['type']?></td><td><?=\Core\Helpers::e($r['doc_number'])?></td><td><?=\Core\Helpers::e($r['subject'])?></td><td><?=\Core\Helpers::e($r['status'])?></td></tr><?php endforeach;?></table>
