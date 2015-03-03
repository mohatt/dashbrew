<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?= $layout_title; ?></title>

  <link href="<?= $_asset('/lib/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet" />
  <link href="<?= $_asset('/lib/font-awesome/css/font-awesome.min.css'); ?>" rel="stylesheet" />
  <link href="<?= $_asset('/css/dashboard.css'); ?>" rel="stylesheet" />

  <script type="text/javascript" src="<?= $_asset('/lib/jquery/jquery.min.js'); ?>"></script>
  <script type="text/javascript" src="<?= $_asset('/lib/jquery/jquery.cookie.min.js'); ?>"></script>
  <script type="text/javascript" src="<?= $_asset('/lib/bootstrap/js/bootstrap.min.js'); ?>"></script>
  <script type="text/javascript" src="<?= $_asset('/lib/list.js/list.min.js'); ?>"></script>
  <script type="text/javascript" src="<?= $_asset('/lib/list.js/list.pagination.min.js'); ?>"></script>
  <script type="text/javascript" src="<?= $_asset('/js/dashboard.min.js'); ?>"></script>
</head>
<body>
  <div id="page-wrapper" class="<?= !isset($_COOKIE['sb-active']) || $_COOKIE['sb-active'] == "1" ? "active" : ""; ?>">

    <!-- Sidebar -->
    <div id="sidebar-wrapper">
      <ul class="sidebar">
        <li class="sidebar-main">
          <a id="toggle-sidebar" href="javascript:void(0);">
            <?= $app->getName(); ?>
            <span class="menu-icon glyphicon glyphicon-transfer"></span>
          </a>
        </li>
        <li class="sidebar-title"><span>NAVIGATION</span></li>
        <li class="sidebar-list">
          <a href="<?= $_url('/'); ?>" class="<?= $_is_url('/') ? 'active' : ''; ?>">Dashboard <span class="menu-icon fa fa-tachometer"></span></a>
        </li>
        <li class="sidebar-list">
          <a href="<?= $_url('/projects'); ?>" class="<?= $_is_url('/projects') ? 'active' : ''; ?>">Projects <span class="menu-icon fa fa-file-code-o"></span></a>
        </li>
        <li class="sidebar-title separator"><span>Utilities</span></li>
        <li class="sidebar-list">
            <a href="<?= $_url('/server/coderunner'); ?>" class="<?= $_is_url('/server/coderunner') ? 'active' : ''; ?>">Code Runner <span class="menu-icon fa fa-terminal"></span></a>
        </li>
        <?php if(!empty($layout_shortcuts)): ?>
        <li class="sidebar-title separator"><span>QUICK LINKS</span></li>
        <?php foreach($layout_shortcuts as $shortcut): ?>
        <li class="sidebar-list">
            <a href="<?= $shortcut['url'] ?>" target="_blank"><?= $shortcut['title'] ?> <span class="menu-icon fa fa-external-link"></span></a>
        </li>
        <?php endforeach; ?>
        <?php endif; ?>
      </ul>
      <div class="sidebar-footer">
        <div class="col-xs-4">
          <a href="https://github.com/mdkholy/dashbrew" target="_blank">Github</a>
        </div>
        <div class="col-xs-4">
          <a href="https://github.com/mdkholy/dashbrew/blob/master/README.md" target="_blank">About</a>
        </div>
        <div class="col-xs-4">
          <a href="https://github.com/mdkholy/dashbrew/issues" target="_blank">Support</a>
        </div>
      </div>
    </div>
    <!-- End Sidebar -->

    <div id="content-wrapper">
      <div class="page-content">

        <!-- Header Bar -->
        <div class="row header">
          <div class="col-xs-12">
            <div class="meta">
              <div class="page">
                  <?= $layout_title; ?>
              </div>
              <div class="breadcrumb-links">
                <?= implode(" / ", $layout_breadcrumbs); ?>
              </div>
            </div>
          </div>
        </div>
        <!-- End Header Bar -->

        <!-- Main Content -->
        <?= $content; ?>

      </div><!-- End Page Content -->
    </div><!-- End Content Wrapper -->
  </div><!-- End Page Wrapper -->
</body>
</html>
