<ul class="list-group">
<?php foreach($projects as $id => $project): ?>
<li class="list-group-item">
    <i class="list-group-item-icon fa fa-file-code-o"></i>
    <h4 class="list-group-item-heading"><?= $project['title'] ?></h4>
    <div class="list-group-item-subheading">
        <a href="<?= $project['http'] ?>" target="_blank"><?=  $project['host'] ?></a>
    </div>
    <a class="list-group-item-btn btn btn-default btn-sm" href="javascript:void(0);" onclick="javascript:$.openProjectInfo('<?= $_url('/projects/info/' . $id); ?>');">
        <span class="fa fa-external-link"></span> View
    </a>
</li>
<?php endforeach; ?>
</ul>
