<div class="row">
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div id="stats-projects"></div>
    </div>
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div id="stats-databases"></div>
    </div>
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div id="stats-phps"></div>
    </div>
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div id="stats-uptime"></div>
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
    // Stats widgets
    $('#stats-projects').widget({
        url: '<?= $_url('/home/widget/stats-projects') ?>',
    });
    $('#stats-databases').widget({
        url: '<?= $_url('/home/widget/stats-databases') ?>',
    });
    $('#stats-phps').widget({
        url: '<?= $_url('/home/widget/stats-phps') ?>',
    });
    $('#stats-uptime').widget({
        url: '<?= $_url('/home/widget/stats-uptime') ?>',
    });

    // Recent projects widget
    $('#recent-projects').widget({
        url: '<?= $_url('/projects/widget/recent') ?>',
        title: 'Recent Projects',
        class: 'no-padding'
    });

    // Installed phps widget
    $('#installed-phps').widget({
        url: '<?= $_url('/server/widget/phps') ?>',
        title: 'Installed PHPs',
        class: 'no-padding'
    });
});
</script>