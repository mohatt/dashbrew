<form class="form" role="form">
    <div class="form-group">
        <label>PHP Version</label>
        <select class="code-version form-control">
            <optgroup label="System">
                <option value="system"><?= $systemPhp ?></option>
            </optgroup>
            <?php if(count($phps) > 0): ?>
            <optgroup label="Phpbrew">
            <?php foreach($phps as $build => $meta): ?>
                <option value="<?= $build ?>" <?= !$meta['running'] ? 'disabled' : '' ?>><?= $build ?></option>
            <?php endforeach; ?>
            </optgroup>
            <?php endif; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Code</label>
        <div class="code-editor form-control"></div>
    </div>
</form>
