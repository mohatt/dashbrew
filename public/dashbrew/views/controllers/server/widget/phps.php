<ul class="projects-list list-group">
    <li class="list-group-item" onclick="$.switchListItemExtra(this)">
        <div class="project-info">
            <h4>System (<?= $systemPhp ?>)</h4>
            <span class="label label-info">System</span>
            <span class="label label-success">Running</span>
        </div>
        <div class="list-group-item-extra">
            <dl>
                <dt>PHP Info</dt>
                <dd><a href="#">View</a></dd>
            </dl>
        </div>
    </li>
    <?php foreach($phps as $version => $meta): ?>
    <li class="list-group-item" onclick="$.switchListItemExtra(this)">
        <div class="project-info">
            <h4><?= $version ?></h4>
            <?php if(!empty($meta['default'])): ?>
                <span class="label label-info">Default</span>
            <?php endif; ?>
            <span class="label label-success"><?= $meta['running'] ? 'Running' : 'Not Running' ?></span>
        </div>
        <div class="list-group-item-extra">
            <dl>
                <dt>PHP Info</dt>
                <dd><a href="#">View</a></dd>
                <dt>Variants</dt>
                <?php if(empty($meta['variants'])): ?>
                    <dd>None</dd>
                <?php else: ?>
                    <dd><?= $meta['variants'] ?></dd>
                <?php endif; ?>
            </dl>
        </div>
    </li>
    <?php endforeach; ?>
</ul>