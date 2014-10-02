<div class="row">
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div class="widget">
            <div class="widget-body">
                <div class="widget-icon green pull-left">
                    <i class="fa fa-file-code-o"></i>
                </div>
                <div class="widget-content pull-left">
                    <div class="title"><?= false !== $stats['projects'] ? $stats['projects'] : 'N/A' ; ?></div>
                    <div class="comment">projects</div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div class="widget">
            <div class="widget-body">
                <div class="widget-icon green pull-left">
                    <i class="fa fa-database"></i>
                </div>
                <div class="widget-content pull-left">
                    <div class="title"><?= false !== $stats['databases'] ? $stats['databases'] : 'N/A' ; ?></div>
                    <div class="comment">databases</div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div class="widget">
            <div class="widget-body">
                <div class="widget-icon green pull-left">
                    <i class="fa fa-cogs"></i>
                </div>
                <div class="widget-content pull-left">
                    <div class="title"><?= false !== $stats['phps'] ? $stats['phps'] : 'N/A' ; ?></div>
                    <div class="comment">installed phps</div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    <div class="spacer visible-xs"></div>
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div class="widget">
            <div class="widget-body">
                <div class="widget-icon green pull-left">
                    <i class="fa fa-clock-o"></i>
                </div>
                <div class="widget-content pull-left">
                    <div class="title"><?= false !== $stats['uptime'] ? implode(" ", $stats['uptime']) : 'N/A' ; ?></div>
                    <div class="comment">uptime</div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div id="recent-projects"></div>
    </div>
    <div class="col-lg-6">
        <div id="installed-phps"></div>
    </div>
</div>
<script>
$(document).ready(function(){
    $('#recent-projects').widget({
        url: '<?= $_url('/projects/widget/recent') ?>',
        title: '<i class="fa fa-file-code-o"></i> Recent Projects',
        class: 'no-padding'
    });
    $('#installed-phps').widget({
        url: '<?= $_url('/server/widget/phps') ?>',
        title: '<i class="fa fa-file-code-o"></i> Installed PHPs',
        class: 'no-padding'
    });
});
</script>