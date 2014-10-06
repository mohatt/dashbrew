<div class="row">
    <div class="col-lg-12">
        <div id="widget-projects"></div>
    </div>
</div>
<script>
$(document).ready(function(){
    $('#widget-projects').widget({
        url: '<?= $_url('/projects/widget/grid') ?>',
        class: 'no-padding',
        title: '<input type="text" placeholder="Search" class="form-control input-sm pull-left"> \
                <div class="btn-group pull-left"> \
                    <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown"> \
                        Sort by <span class="caret"></span> \
                    </button> \
                    <ul class="dropdown-menu" role="menu"> \
                        <li data-sort="title" class="active"><a href="javascript:void(0);">Title</a></li> \
                        <li data-sort="created"><a href="javascript:void(0);">Date Created</a></li> \
                        <li data-sort="modified"><a href="javascript:void(0);">Date Modified</a></li> \
                        <li class="divider"></li> \
                        <li data-sortdir="asc" class="active"><a href="javascript:void(0);">Ascending</a></li> \
                        <li data-sortdir="desc"><a href="javascript:void(0);">Descending</a></li> \
                    </ul> \
                </div> \
                <div class="clearfix"></div>',
        onSuccess: function($el){
            var userList = new List('projects-grid', {
                valueNames: [
                    'entry-title',
                    'entry-url',
                    'entry-created',
                    'entry-modified'
                ],
                listClass: 'data-grid',
                page: 18,
                plugins: [
                    ListPagination()
                ]
            });

            var $wheader = $el.find('.widget-header');

            $wheader.find('input:first').on('input', function(){
                userList.search(this.value);
            }).trigger('input');

            userList.sort('entry-' + $wheader.find("li.active[data-sort]").data('sort'), {
                order: $wheader.find("li.active[data-sortdir]").data('sortdir')
            });

            $wheader.find('li[data-sort]').on('click', function(){
                var $this = $(this);
                userList.sort('entry-' + $this.data('sort'), {
                    order: $wheader.find('li.active[data-sortdir]').data('sortdir')
                });

                $this.siblings('[data-sort]').removeClass('active');
                $this.addClass('active');
            });

            $wheader.find('li[data-sortdir]').on('click', function(){
                var $this = $(this);
                userList.sort('entry-' + $wheader.find('li.active[data-sort]').data('sort'), {
                    order: $this.data('sortdir')
                });

                $this.siblings('[data-sortdir]').removeClass('active');
                $this.addClass('active');
            });

            var $pagination = $el.find('ul.pagination');
            if($pagination.find('li').length <= 1){
                $pagination.hide();
            }
            else {
                $pagination.show();
            }
        }
    });
});
</script>