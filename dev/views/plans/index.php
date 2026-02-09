<h4>الباقات والوحدات الافتراضية</h4>
<form method="post" class="card card-body bg-white">
<input type="hidden" name="_csrf" value="<?=\Core\Helpers::e($csrf)?>">
<table class="table table-bordered"><tr><th>الباقة</th><?php foreach($modules as $m):?><th><?=$m['key_name']?></th><?php endforeach;?></tr><?php foreach($plans as $p):?><tr><td><?=$p['name']?></td><?php foreach($modules as $m):?><td><input type="checkbox" name="pm[<?=$p['id']?>][<?=$m['id']?>]" <?=($map[$p['id']][$m['id']]??0)?'checked':''?>></td><?php endforeach;?></tr><?php endforeach;?></table>
<button class="btn btn-primary">حفظ إعدادات الباقات</button>
</form>
