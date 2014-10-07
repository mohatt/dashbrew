<link href="<?= $_asset('/lib/codemirror/codemirror.min.css'); ?>" rel="stylesheet" />
<script type="text/javascript" src="<?= $_asset('/lib/codemirror/codemirror.min.js'); ?>"></script>
<div class="row">
    <div class="col-lg-6">
        <div id="widget-coderunner-input"></div>
    </div>
    <div class="col-lg-6">
        <div id="widget-coderunner-output"></div>
    </div>
</div>
<script>
$(document).ready(function(){
    $('#widget-coderunner-input').widget({
        url: '<?= $_url('/server/coderunner/widget/input') ?>',
        title: 'Input <button class="btn btn-sm btn-default pull-right widget-btn-run"><span class="fa fa-play-circle"></span> Run</button>',
        onInit: function ($widget) {
            $widget.header.children('.widget-btn-run').click(function(){
                if(!$widget.obj.cm){
                    return false;
                }

                var outputWidget = $('#widget-coderunner-output').data('widget');
                if(outputWidget){
                    outputWidget.opts.ajax = $.extend(outputWidget.opts.ajax, {
                        type: 'POST',
                        data: {
                            version: $widget.data.find('select.code-version').val(),
                            code: $widget.obj.cm.getValue()
                        }
                    });

                    outputWidget.load();
                }
            });
        },
        onSuccess: function ($widget) {
            $widget.obj.cm = CodeMirror(function(elt){
                $(elt).appendTo($widget.data.find('.code-editor:first'));
            }, {
                mode:  "php",
                lineNumbers: true,
                theme: 'eclipse',
                value: "\<\?php\n"
            });
        }
    });

    $('#widget-coderunner-output').widget({
        url: '<?= $_url('/server/coderunner/widget/output') ?>',
        title: 'Output'
    });
});
</script>
