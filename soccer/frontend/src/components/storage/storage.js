import {DEFAULT_FILTER} from '../filter';
import {DEFAULT_SORT} from '../sort';

class Storage {
    storage = window.localStorage;//this.activate();

    activate() {
        try {
            var x = '__storage_test__';
            window.localStorage.setItem(x, x);
            window.localStorage.removeItem(x);
            return window.localStorage;
        }
        catch(e) {
            return null;
        }
    }

    reset() {
        if (!this.storage) {
            return;
        }
        this.storage.clear();
    }

    set(props) {
        if (!this.storage) {
            return;
        }
        const {filter, sort, favorites} = props;
        if (filter !== undefined) this.storage.setItem('filter', filter);
        if (sort !== undefined) this.storage.setItem('sort', sort);
        if (favorites !== undefined) this.storage.setItem('favorites', JSON.stringify(favorites));
    }

    get() {
        if (!this.storage) {
            return {};
        }
        const filter = this.getIntItem('filter', DEFAULT_FILTER);
        const sort = this.getIntItem('sort', DEFAULT_SORT);
        const favorites = this.getJsonItem('favorites', []);
        return {filter, sort, favorites};
    }

    getIntItem(name, def) {
        let item = this.storage.getItem(name);
        if (!item) {
            return def;
        }
        item = parseInt(item);
        if (isNaN(item)) {
            return def;
        }
        return item;
    }

    getJsonItem(name, def) {
        let item = this.storage.getItem(name);
        if (!item) {
            return def;
        }
        try {
            return JSON.parse(item);
        } catch (e) {
            return def;
        }
    }
}

export default Storage;