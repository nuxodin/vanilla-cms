'use strict';
{
    let itm = c1.itm = class {
        $set(value){
            if (this.value !== value) {
                this.value = value;
                this.trigger('change');
            }
            this.trigger('set');
        }
        $get(){
            return this.value;
        }
        $key(){
            return this.$_key;
        }
        $uuid(){
            if (this.$parent()) {
                return this.$parent().$uuid + '/' + this.$key();
            }
            return this.uuid;
        }
        $parent(){
            return this.$_parent;
        }
    };
    c1.ext(c1.Eventer, itm.prototype);

    /* extensions */
    c1.itm.prototype.bindToInputValue = function(input){
        this.value === undefined && this.$set(input.value); // which is master (set this.value or the inputs value?)
        input.addEventListener('input',e=>{
            this.$set(input.value);
        });
        this.on('change', ()=>{
            input.value = this.$get();
        });
    };
    c1.itm.prototype.storeInLocalStorage = function(){
        if (this.$uuid() === undefined) throw('$uuid() needed!');
        const lid = 'c1_itm_uuid_'+this.$uuid();
        this.$set( localStorage.getItem(lid) );
        this.on('change',()=>{
            localStorage.setItem(lid, this.value);
        });
    };
}


/*
{
    var collection = c1.Collection = class {
        construct(obj){
            obj !== undefined && this.set(obj);
            this.data = {};
        }
        set(n, v) {
    		if (typeof n === 'object') {
    			for (var key in n) {
    				n.hasOwnProperty(key) && this.set(key, n[key]);
    			}
    			return;
    		}
    		var old_value = this.data[n];
    		if (typeof v === 'object') {
    			this.data[n] = new collection(v);
    		} else {
    			this.data[n] = v;
    		}
    		this.trigger('set', {name:n, value:v, old:old_value});
    	}
    	get(n) {
    		return this.data[n];
    	}
    	getAll() {
    		const ret = {};
    		for (const key in this.data) {
    			if (this.data[key] instanceof collection) {
    				ret[key] = this.data[key].getAll();
    			} else {
    				ret[key] = this.data[key];
    			}
    		}
    		return ret;
    	}
    };
    c1.ext(c1.Eventer, collection.prototype);
}
*/
