/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
class c1FullScreenPopup {
    show() {
        var el = this.el();
        document.body.appendChild(el);
        document.documentElement.style.overflow = 'hidden';
        el.c1ZTop();
        el.focus();
        el.addEventListener('keyup',e=>{
            if (e.target.isContentEditable || e.target.form !== undefined) return;
            if (e.which === 27) this.hide();
        });
        //el.webkitRequestFullscreen();
    }
    hide() {
        document.documentElement.style.overflow = '';
        this.el().remove();
    }
    el(selector) {
        if (!this._root) {
            this._root = document.createElement('div');
            this._root.setAttribute('tabindex','-1');
            this._root.style.cssText = 'position:fixed; top:0; left:0; right:0; bottom:0; background:#fff; display:flex; flex-flow:column';
        }
        return selector ? this._root.querySelector(selector) : this._root;
    }
}
