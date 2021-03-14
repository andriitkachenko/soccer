class GameUtils {
    static getHalfMaxShots(g) {
        return Math.max(this.getHostShots(g), this.getGuestShots(g));
    }
    static getHostShots(g) {
        return g.ht 
            ? g.host_stat.sh - g.ht.host_stat.sh
            : g.host_stat.sh    
    }
    static getGuestShots(g) {
        return g.ht 
            ? g.guest_stat.sh - g.ht.guest_stat.sh
            : g.guest_stat.sh
    }
}
export default GameUtils;