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
        onInit: function($widget){

            var $header = $widget.header;

            $header.find('input:first').on('input', function(){
                if(!$widget.obj.list){
                    return false;
                }

                $widget.obj.list.search(this.value);
            });

            $header.find('li[data-sort]').on('click', function(){
                if(!$widget.obj.list){
                    return false;
                }

                var $this = $(this);
                $widget.obj.list.sort('entry-' + $this.data('sort'), {
                    order: $header.find('li.active[data-sortdir]').data('sortdir')
                });

                $this.siblings('[data-sort]').removeClass('active');
                $this.addClass('active');
            });

            $header.find('li[data-sortdir]').on('click', function(){
                if(!$widget.obj.list){
                    return false;
                }

                var $this = $(this);
                $widget.obj.list.sort('entry-' + $header.find('li.active[data-sort]').data('sort'), {
                    order: $this.data('sortdir')
                });

                $this.siblings('[data-sortdir]').removeClass('active');
                $this.addClass('active');
            });
        },
        onSuccess: function($widget){

            $widget.obj.list = new List('projects-grid', {
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

            $widget.header.find('input:first').trigger('input');

            $widget.obj.list.sort('entry-' + $widget.header.find("li.active[data-sort]").data('sort'), {
                order: $widget.header.find("li.active[data-sortdir]").data('sortdir')
            });

            var $pagination = $widget.data.find('ul.pagination');
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
