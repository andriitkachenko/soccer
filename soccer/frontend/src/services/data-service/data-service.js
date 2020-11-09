// http://localhost:3000?data=http://localhost:9000

class DataService {
    _apiBaseUrl = null;

    async getData(params) {
        this._apiBaseUrl = this._apiBaseUrl ? this._apiBaseUrl : this.getBaseUrl();
        try {
            const res = await fetch(this._apiBaseUrl, {
                method: 'POST',
                body: JSON.stringify(params)
            });
            if (!res.ok) {
                return false;
            }
            return await res.json();
        } catch (e) {
            return false;
        }
    }

    getLastStats = async () => {
        return await this.getData({op : 'last_stat'});
    }

    getBaseUrl = () => {
        const url = new URL(window.location.href);
        const dataHost = url.searchParams.get("data");
        const customDataHost = dataHost && window.location.hostname === "localhost";
    
        return (customDataHost ? dataHost : 'http://livesoccer.96.lt') + '/api/data/index.php';
    }
}

export default DataService;