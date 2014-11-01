<?php $_helper('yamlprint'); ?>
<div id="project-info">
    <nav class="navbar navbar-default" role="navigation">
        <ul class="nav navbar-nav">
            <li class="active"><a href="#tab-general" role="tab" data-toggle="tab">General</a></li>
            <li><a href="#tab-vhost" role="tab" data-toggle="tab">Vhost</a></li>
            <?php if(!empty($project['php'])): ?>
                <li><a href="#tab-php" role="tab" data-toggle="tab">PHP</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="tab-content navbar-content">
        <div class="tab-pane active" id="tab-general">
            <dl>
                <dt>Id</dt>
                <dd><?= $id ?></dd>
                <dt>Path</dt>
                <dd><?= $project['_path'] ?></dd>
                <dt>Title</dt>
                <dd><?= $project['title'] ?></dd>
                <?php if(!empty($project['http'])): ?>
                    <dt>Http</dt>
                    <dd><a href="<?= $project['http'] ?>" target="_blank"><?= $project['http'] ?></a></dd>
                <?php endif; ?>
                <?php if(!empty($project['https'])): ?>
                    <dt>Https</dt>
                    <dd><a href="<?= $project['https'] ?>" target="_blank"><?= $project['https'] ?></a></dd>
                <?php endif; ?>
            </dl>
        </div>
        <div class="tab-pane" id="tab-vhost">
            <dl>
                <dt>Server Name</dt>
                <dd><?= _yamlprint_string($project['vhost'], 'servername') ?></dd>
                <?php if(!empty($project['vhost']['serveraliases'])): ?>
                    <dt>Server Aliases</dt>
                    <dd><?= _yamlprint_array($project['vhost'], 'serveraliases') ?></dd>
                <?php endif; ?>
                <dt>SSL</dt>
                <dd><?= _yamlprint_bool($project['vhost'], 'ssl') ?></dd>
                <?php if(empty($project['vhost']['reverseproxy'])): ?>
                <dt>Document Root</dt>
                <dd><?= str_replace([
                        '${dir}',
                        '${root}',
                    ], [
                      dirname($project['_path']),
                      '/vagrant/public'
                    ], _yamlprint_string($project['vhost'], 'docroot', '${dir}')) ?></dd>
                <dt>Options</dt>
                <dd><?= _yamlprint_array($project['vhost'], 'options', ['Indexes','FollowSymLinks']) ?></dd>
                <dt>Override</dt>
                <dd><?= _yamlprint_array($project['vhost'], 'override', ['All']) ?></dd>
                <?php else: ?>
                <dt>Reverse Proxy</dt>
                <dd><?= _yamlprint_hash($project['vhost'], 'reverseproxy') ?></dd>
                <?php endif; ?>
            </dl>
        </div>
        <?php if(!empty($project['php'])): ?>
        <div class="tab-pane" id="tab-php">
            <dt>PHP Build</dt>
            <dd><?= _yamlprint_string($project['php'], 'build', 'None') ?></dd>
        </div>
        <?php endif; ?>
    </div>

</div>