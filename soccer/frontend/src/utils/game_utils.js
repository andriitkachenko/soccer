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
    static getHalfMaxShotsOnGoal(g) {
        return Math.max(this.getHostShotsOnGoal(g), this.getGuestShotsOnGoal(g));
    }
    static getHostShotsOnGoal(g) {
        return g.ht 
            ? g.host_stat.sg - g.ht.host_stat.sg
            : g.host_stat.sg    
    }
    static getGuestShotsOnGoal(g) {
        return g.ht 
            ? g.guest_stat.sg - g.ht.guest_stat.sg
            : g.guest_stat.sg
    }
    static isLast15(g) {
        return (g.time >= 30 && g.state === 1) || (g.time >= 75 && g.state === 3)
    }
    static isActive(g) {
        return this.isLast15(g) 
            &&  (this.getHalfMaxShots(g) >= 7 || this.getHalfMaxShotsOnGoal(g) >= 4);
    }
}
export default GameUtils;