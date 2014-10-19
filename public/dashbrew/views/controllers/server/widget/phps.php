<ul class="list-group">
    <li class="list-group-item">
        <i class="list-group-item-icon fa fa-code-fork"></i>
        <h4 class="list-group-item-heading"><?= $systemPhp ?></h4>
        <div class="list-group-item-subheading">
            <span class="label label-info">system</span>
            <span class="label label-success">running</span>
        </div>
        <a class="list-group-item-btn btn btn-default btn-sm" href="<?= $_url("/server/phpinfo/system") ?>" target="_blank">
            <span class="fa fa-external-link"></span> Info
        </a>
    </li>
    <?php foreach($phps as $build => $meta): ?>
    <li class="list-group-item">
        <i class="list-group-item-icon fa fa-code-fork"></i>
        <h4 class="list-group-item-heading"><?= $build ?></h4>
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
        <?php if(!empty($meta['running'])): ?>
        <a class="list-group-item-btn btn btn-default btn-sm" href="<?= $_url("/server/phpinfo/{$build}") ?>" target="_blank">
            <span class="fa fa-external-link"></span> Info
        </a>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ul>