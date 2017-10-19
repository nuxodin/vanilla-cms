'use strict';
{

    let tables = new Set();
    c1.onElement('td[c1-hideable],th[c1-hideable]',function(element){
        let table = element.parentElement.parentElement.parentElement;
        tables.add(table);
        document.addEventListener('DOMContentLoaded',function(){
//            renderTable(table)
        })
    });

    function loopSameCol(td,cb) {
		let index = td.cellIndex;
        let table = td.parentNode.parentNode.parentNode;
        for (let trgroup of table.children) {
            for (let tr of trgroup.children) {
                if (!tr.children[index]) {
                    console.log(table.firstElementChild.nextElementSibling.children.length)
                }
                cb(tr.children[index]);
            }
        }

    }
    // version with css, bugy!
    function renderTable(table) {
        let available = table.parentNode.offsetWidth;
        let firstTr = table.firstElementChild.firstElementChild;

        let style = table.c1Find('>style');
        if (!style) {
            style = document.createElement('style');
            table.append(style);
        }
        // alle einblenden
        let css = '';
        // breite berechnen (nur einmal)
        if (table._hidableTotalWidth === undefined) {
            table._hidableTotalWidth = 0;
            for (let i=0, td; td=firstTr.children[i++];){
                td._hidableWidth = td.offsetWidth;
                table._hidableTotalWidth += td._hidableWidth;
            }
        }
        let totalWidth = table._hidableTotalWidth;
        // ausblenden solange kein platz
        if (totalWidth > available) {
            for (let i=firstTr.children.length-1, td; td=firstTr.children[i--];){
                if (td.hasAttribute('c1-hideable')) {
                    css += '#'+table.c1Id()+' > * > tr > :nth-child('+(td.cellIndex+1)+') { display:none; } \n';
                    let width = totalWidth -= td._hidableWidth;
                    if (width <= available) break;
                }
            }
        }
        style.innerHTML = css;
    }

    function xrenderTable(table) {
        let available = table.parentNode.offsetWidth;
        let firstTr = table.firstElementChild.firstElementChild;

        // alle einblenden
        for (let i=0, td; td=firstTr.children[i++];){
            if (td.hasAttribute('c1-hideable')) {
                loopSameCol(td,function(td){ td.hidden = false; })
            }
        }
        // breite berechnen (nur einmal)
        if (table._hidableTotalWidth === undefined) {
            table._hidableTotalWidth = 0;
            for (let i=0, td; td=firstTr.children[i++];){
                td._hidableWidth = td.offsetWidth;
                table._hidableTotalWidth += td._hidableWidth;
            }
        }
        let totalWidth = table._hidableTotalWidth;
        // ausblenden solange kein platz
        if (totalWidth <= available) return; // kein platz
        for (let i=firstTr.children.length-1, td; td=firstTr.children[i--];){
            if (td.hasAttribute('c1-hideable')) {
                loopSameCol(td,function(td){ td.hidden = true; })
                let width = totalWidth -= td._hidableWidth;
                if (width <= available) break;
            }
        }
    }
    function listener(){
        for (let table of tables) renderTable(table)
    }
    addEventListener('resize',listener)
}
