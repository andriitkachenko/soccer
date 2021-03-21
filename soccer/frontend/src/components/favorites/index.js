class Favorites {
    constructor() {
        this.favorites = [];
    }
    add(gameId) {
        if (gameId && !this.favorites.includes(gameId)) {
            this.favourites.push(gameId);
        }
    }
    remove(gameId) {
        if (gameId && this.favorites.includes(gameId)) {
            this.favorites.filter((id) => id !== gameId);
        }
    }
    contains(gameId) {
        return this.favorites.contains(gameId);
    }
}

export default Favorites;