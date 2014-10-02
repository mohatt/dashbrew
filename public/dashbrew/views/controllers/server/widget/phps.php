<ul class="projects-list list-group">
    <li class="list-group-item" onclick="$.switchListItemExtra(this)">
        <i class="list-group-item-icon fa fa-cogs pull-left"></i>
        <h4 class="list-group-item-heading"><?= $systemPhp ?></h4>
        <div class="list-group-item-subheading">
            <span class="label label-info">system</span>
            <span class="label label-success">running</span>
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
        <i class="list-group-item-icon fa fa-cogs pull-left"></i>
        <h4 class="list-group-item-heading"><?= $version ?></h4>
        <div class="list-group-item-subheading">
            <span class="label label-info">phpbrew</span>
            <?php if(!empty($meta['default'])): ?>
                <span class="label label-warning">default</span>
            <?php endif; ?>
            <?php if(!empty($meta['running'])): ?>
            <span class="label label-success">running</span>
            <?php else: ?>
            <span class="label label-danger">stopped</span>
            <?php endif; ?>
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