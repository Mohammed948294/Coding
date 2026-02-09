<h3 class="mb-3">لوحة تحكم المطور</h3>
<div class="row g-3 mb-3"><div class="col"><div class="card"><div class="card-body">المؤسسات: <?=$summary['tenants']?></div></div></div><div class="col"><div class="card"><div class="card-body">الوثائق: <?=$summary['docs']?></div></div></div><div class="col"><div class="card"><div class="card-body">سجلات آخر 24 ساعة: <?=$summary['logs']?></div></div></div></div>
<div class="row">
<div class="col-md-6"><div class="card mb-3"><div class="card-body"><h5>استهلاك التخزين لكل مؤسسة</h5><table class="table"><tr><th>المؤسسة</th><th>MB</th></tr><?php foreach($storage as $s):?><tr><td><?=\Core\Helpers::e($s['name'])?></td><td><?=number_format(((int)$s['bytes'])/1048576,2)?></td></tr><?php endforeach;?></table></div></div></div>
<div class="col-md-6"><div class="card mb-3"><div class="card-body"><h5>ملخص السجلات</h5><table class="table"><tr><th>Actor</th><th>Action</th><th>Entity</th><th>Date</th></tr><?php foreach($logs as $l):?><tr><td><?=$l['actor_type']?></td><td><?=$l['action']?></td><td><?=$l['entity']?></td><td><?=$l['created_at']?></td></tr><?php endforeach;?></table></div></div></div>
</div>
