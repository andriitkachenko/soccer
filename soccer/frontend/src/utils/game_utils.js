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
        return (g.time >= 30 && g.time <= 45 && g.extra === 0 && g.state === 1) 
            || (g.time >= 75 && g.time <= 90 && g.extra === 0 && g.state === 3);
    }
    static isHalfEnding(g) {
        return (g.time >= 30 && g.state === 1) 
            || (g.time >= 75 && g.state === 3)
    }
    static isPredictable(g) {
        const activeHalfEnding = this.isLast15(g) 
            &&  (this.getHalfMaxShots(g) >= 7 || this.getHalfMaxShotsOnGoal(g) >= 4);
        return activeHalfEnding;
    }
}
export default GameUtils;