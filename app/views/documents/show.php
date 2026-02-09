<h4>تفاصيل الوثيقة #<?=$document['id']?></h4>
<div class="card mb-3 shadow-sm"><div class="card-body">
  <p><b>الموضوع:</b> <?=\Core\Helpers::e($document['subject'])?></p>
  <p><b>الحالة:</b> <?=\Core\Helpers::e($document['status'])?></p>
  <p><b>ملاحظات:</b> <?=\Core\Helpers::e($document['notes'])?></p>
  <form method="post" action="/app/public/index.php?r=documents.archive&id=<?=$document['id']?>">
    <input type="hidden" name="_csrf" value="<?=\Core\Helpers::e($archiveCsrf)?>">
    <button class="btn btn-outline-warning btn-sm">أرشفة</button>
  </form>
</div></div>
<div class="card mb-3 shadow-sm"><div class="card-body"><h5>المرفقات</h5><ul><?php foreach($attachments as $a):?><li><a href="/app/public/index.php?r=documents.download&id=<?=$a['id']?>"><?=\Core\Helpers::e($a['original_name'])?></a></li><?php endforeach;?></ul></div></div>
<div class="card shadow-sm"><div class="card-body"><h5>الروابط المرجعية</h5><ul><?php foreach($links as $l):?><li><?=\Core\Helpers::e($l['relation_type'])?>: <?=\Core\Helpers::e($l['doc_number'])?> - <?=\Core\Helpers::e($l['subject'])?></li><?php endforeach;?></ul></div></div>
