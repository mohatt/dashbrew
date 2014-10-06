<div id="projects-grid">
    <ul class="data-grid row">
        <?php foreach($projects as $id => $project): ?>
            <li class="data-grid-col col-lg-4 col-md-6 col-sm-6">
                <div class="data-grid-item">
                    <i class="data-grid-item-icon fa fa-file-code-o pull-left"></i>
                    <h4 class="data-grid-item-heading entry-title"><?= $project['title'] ?></h4>
                    <div class="data-grid-item-subheading entry-url">
                        <a href="<?= $project['http'] ?>" target="_blank"><?=  $project['host'] ?></a>
                    </div>
                    <a class="data-grid-item-btn btn btn-default btn-sm" href="javascript:$.openProjectInfo('<?= $_url('/projects/info/' . $id); ?>');">
                        <span class="fa fa-external-link"></span> View
                    </a>
                    <div style="display: none;">
                        <div class="entry-created"><?= $project['_created'] ?></div>
                        <div class="entry-modified"><?= $project['_modified'] ?></div>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <ul class="pagination data-grid-pagination"></ul>
</div>