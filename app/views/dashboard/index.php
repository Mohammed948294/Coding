<h3 class="mb-4">لوحة التحكم</h3>

<?php $labels=['total'=>'كل الوثائق','inbound'=>'الوارد','outbound'=>'الصادر','today'=>'اليوم']; ?>
<div class="row g-3 mb-4">
<?php foreach (['total','inbound','outbound','today'] as $k): if(in_array($k,$widgets,true)): ?>
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="text-muted"><?=$labels[$k]?></div><div class="fs-3"><?=$stats[$k]??0?></div></div></div></div>
<?php endif; endforeach; ?>
</div>

<div class="card mb-4 shadow-sm"><div class="card-body">
  <h5>الاتجاه الشهري</h5><canvas id="trend" width="900" height="95"></canvas>
</div></div>

<?php if (in_array('activities',$widgets,true)): ?>
<div class="card shadow-sm"><div class="card-body">
  <h5>آخر النشاطات</h5>
  <table class="table table-striped"><tr><th>الإجراء</th><th>الكيان</th><th>التاريخ</th></tr>
    <?php foreach($activities as $a):?><tr><td><?=\Core\Helpers::e($a['action'])?></td><td><?=\Core\Helpers::e($a['entity'])?></td><td><?=$a['created_at']?></td></tr><?php endforeach;?>
  </table>
</div></div>
<?php endif; ?>

<script>
const rows = <?=json_encode(array_reverse($stats['monthly'] ?? []))?>;
const c = document.getElementById('trend'); const x = c.getContext('2d');
x.beginPath(); rows.forEach((r,i)=>{ const px=30+i*70, py=80-(parseInt(r.c,10)*4); if(i===0)x.moveTo(px,py); else x.lineTo(px,py); x.fillText(r.m,px-20,92);}); x.strokeStyle='#0d6efd'; x.stroke();
</script>
