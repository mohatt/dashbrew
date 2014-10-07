<div class="row">
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div id="widget-stats-projects"></div>
    </div>
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div id="widget-stats-databases"></div>
    </div>
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div id="widget-stats-phps"></div>
    </div>
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div id="widget-stats-uptime"></div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div id="widget-recent-projects"></div>
    </div>
    <div class="col-lg-6">
        <div id="widget-installed-phps"></div>
    </div>
</div>
<script>
$(document).ready(function(){
    // Stats widgets
    $('#widget-stats-projects').widget({
        url: '<?= $_url('/home/widget/stats-projects') ?>',
        header: false
    });
    $('#widget-stats-databases').widget({
        url: '<?= $_url('/home/widget/stats-databases') ?>',
        header: false
    });
    $('#widget-stats-phps').widget({
        url: '<?= $_url('/home/widget/stats-phps') ?>',
        header: false
    });
    $('#widget-stats-uptime').widget({
        url: '<?= $_url('/home/widget/stats-uptime') ?>',
        header: false
    });

    // Recent projects widget
    $('#widget-recent-projects').widget({
        url: '<?= $_url('/projects/widget/recent') ?>',
        title: 'Recent Projects',
        class: 'no-padding'
    });

    // installed phps widget
    $('#widget-installed-phps').widget({
        url: '<?= $_url('/server/widget/phps') ?>',
        title: 'Installed PHPs',
        class: 'no-padding'
    });
});
</script>