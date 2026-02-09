<div class="d-flex justify-content-between mb-3">
  <h4><?= $type==='inbound' ? 'الوارد' : 'الصادر' ?></h4>
  <a class="btn btn-primary" href="/app/public/index.php?r=<?=$type?>.create">إضافة</a>
</div>
<table class="table table-striped bg-white shadow-sm">
  <tr><th>#</th><th>الرقم</th><th>الموضوع</th><th>الحالة</th><th>إجراءات</th></tr>
  <?php foreach($documents as $d):?>
    <tr>
      <td><?=$d['id']?></td><td><?=\Core\Helpers::e($d['doc_number'])?></td><td><?=\Core\Helpers::e($d['subject'])?></td><td><?=\Core\Helpers::e($d['status'])?></td>
      <td>
        <a href="/app/public/index.php?r=documents.show&id=<?=$d['id']?>">عرض</a> |
        <a href="/app/public/index.php?r=documents.edit&id=<?=$d['id']?>">تعديل</a>
      </td>
    </tr>
  <?php endforeach;?>
</table>
