{
    let cnt = 0;
    c1.contextMenu = function(root){
        const id = 'qgContextMenu_'+(cnt++);
        const menu = c1.dom.fragment('<menu type=context id="'+id+'">').firstChild;
        root.append(menu);
        root.setAttribute('contextmenu',id);
        return new MenuItem(menu);
    }
    class MenuItem {
        constructor(menu){ this.menu = menu; }
        addItem(label, opt={}) { return this._add('menuitem', label, opt); }
        addMenu(label, opt={}) { return this._add('menu', label, opt); }
        _add(what, label, opt={}) {
            //let root = opt.root || document.documentElement;
            const root = document.documentElement;
            const menu = this.menu;
            const item = c1.dom.fragment('<'+what+' label="'+label+'" icon="'+opt.icon+'">').firstChild;
            opt.onclick && item.addEventListener('click', opt.onclick);
            root.addEventListener('contextmenu', e=>{
                const target = opt.selector ? e.target.closest(opt.selector) : root;
                if (!target) return;
                // if (e.qgContextMenueTarget && e.qgContextMenueTarget !== target) return; // my custom stopPropagation | used for?
                // e.qgContextMenueTarget = target;
                opt.onshow && opt.onshow.bind(item)({currentTarget:target});
                menu.append(item);
                setTimeout(()=>item.remove(),10);
            },true);
            return new MenuItem(item);
        }
    }
    Object.defineProperty(c1,'globalContextMenu',{
        get(){
            delete this.globalContextMenu;
            const Menu = new c1.contextMenu(document.documentElement);
            return this.globalContextMenu = Menu;
        },
        configurable: true
    });
}
