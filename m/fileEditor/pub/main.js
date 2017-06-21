document.addEventListener('DOMContentLoaded',function(){
    'use strict';

    var btn = document.getElementById('saveButton');
    var editorEl = document.getElementById('editor');
    var mime   = editorEl.getAttribute('mime');
    var cmLine = editorEl.getAttribute('line')-1;
    var cmCol  = editorEl.getAttribute('col')-1;

    function saveFile(content){
        btn.style.backgroundColor = '#fea';
        Ask({save: content},{
            onComplete: function(asw){
                if (asw) {
                    btn.style.backgroundColor = '';
                    btn.style.display = 'none';
                }
            }
        });
    };

    btn.addEventListener('click', function(){
        saveFile(editor.getValue());
    });

    function saveEvent(e){
        if (e.keyCode == 83 && e.ctrlKey) {
            btn.dispatchEvent(new Event('click'));
            e.preventDefault();
        }
    }
    var editor = CodeMirror.fromTextArea(editorEl, {
        lineNumbers:  true,
        theme:        'eclipse',
        //mode:         mime,
        mode: {name: mime, globalVars: true},
        extraKeys: {"Ctrl-Space": "autocomplete"},
        lineWrapping: true,
        highlightSelectionMatches: {showToken: /\w/, annotateScrollbar: true},
        matchTags: {bothTags: true},
        showTrailingSpace: true,
        indentWithTabs: true,
        smartIndent: true,
        indentUnit: 4,
        tabSize: 4,
    });
    editor.focus();
    editor.getWrapperElement().ownerDocument.addEventListener('keydown', saveEvent, 0);

    editor.on('change',function(){
        btn.style.display = 'block';
        btn.style.backgroundColor = '#faa';
    });

    if (cmLine !== '') {
        cmLine = parseInt(cmLine);
        setTimeout(function() {
            editor.setCursor(cmLine, cmCol);
            editor.addLineClass(cmLine, null, "markLine");
            var line = $('.CodeMirror-lines .markLine');
            if (!line.offset()) return;
            var h = line.parent();
            $('.CodeMirror-scroll').scrollTop(0).scrollTop(line.offset().top - $('.CodeMirror-scroll').offset().top - Math.round($('.CodeMirror-scroll').height()/4));
        }, 200);
    }

})
