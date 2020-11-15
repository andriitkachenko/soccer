class Utils {
    static getLocalSeconds(utcString) {
        const local = utcString ? Utils.getDateLocal(utcString) : new Date();
        return Math.floor(local.getTime() / 1000);
    }

    static getDateLocal(utcString) {
        var local = new Date(Utils.makeISOUTCString(utcString)).toString();
        return new Date(local);
    }

    static getLocalTimeString(utcString) {
        var local = Utils.getDateLocal(utcString);
        const h = local.getHours();        
        const m = local.getMinutes();        
        return (h < 10 ? '0' : '') + h + ":" + (m < 10 ? '0' : '') + m;
    }

    static makeISOUTCString(utcString) {
        return utcString.replace(' ', 'T') + 'Z';
    }    

    static getGameMinutes(game) {
        const startUTC = Utils.getLocalSeconds(game.start_real);
        const nowUTC = Utils.getLocalSeconds();
        var m = Math.floor(((nowUTC - startUTC) / 60)) + 45 * (+(game.state > 2));
        return m;
    }

    static makeSuperscript(s, prefix = '')  {
        return s ? <span className='superscript'>{prefix}{s}</span> : '';
    }
}

export default Utils;