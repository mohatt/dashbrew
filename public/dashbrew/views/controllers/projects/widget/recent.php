<ul class="projects-list list-group">
<?php foreach($projects as $id => $project): ?>
<li class="list-group-item" onclick="$.switchListItemExtra(this)">
    <div class="project-info">
        <h4 class="list-group-item-heading"><?= $project['title'] ?></h4>
        <a href="<?= $project['http'] ?>" target="_blank" class="list-group-item-text"><?=  $project['host'] ?></a>
    </div>
    <div class="list-group-item-extra">
        <dl>
            <?php if(!empty($project['php'])): ?>
            <dt>PHP Version</dt>
            <dd><?= $project['php'] ?></dd>
            <?php endif; ?>
            <dt>SSL</dt>
            <?php if(empty($project['https'])): ?>
            <dd>Disabled</dd>
            <?php else: ?>
            <dd>Enabled</dd>
            <dt>SSL Host</dt>
            <dd><a href="<?= $project['https'] ?>" target="_blank"><?=  $project['https'] ?></a></dd>
            <?php endif; ?>
        </dl>
    </div>
</li>
<?php endforeach; ?>
</ul>