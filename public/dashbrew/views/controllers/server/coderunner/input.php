<form class="form" role="form">
    <div class="form-group">
        <label>PHP Version</label>
        <select class="code-version form-control">
            <optgroup label="System">
                <option value="system"><?= $systemPhp ?></option>
            </optgroup>
            <optgroup label="Phpbrew">
            <?php foreach($phps as $version => $meta): ?>
                <option value="<?= $version ?>"><?= $version ?></option>
            <?php endforeach; ?>
            </optgroup>
        </select>
    </div>

    <div class="form-group">
        <label>Code</label>
        <div class="code-editor form-control"></div>
    </div>
</form>
