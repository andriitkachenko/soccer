import  { Utils, GameUtils } from '../../utils';

import Game from '../game';
import { Loader } from '../blocks';

import './app_games.css';
import { NO_FILTER, FILTER_15MIN, FILTER_FAVORITE, FILTER_HALFTIME, FILTER_PREDICTABLE, getEmptyFilteredListText } from '../filter';
import { SORT_TIME, SORT_SHOTS, SORT_LEAGUE } from '../sort';

const AppGames = ({ games, filter, sort, favorites, setFavorites }) => {

    const emptyListView = (block) => {
        return <div className='app-games no-game'>{block}</div>;
    }
    if (!games) {
        return emptyListView(<Loader/>);
    }
    if (!games.length) {
        return emptyListView(getEmptyFilteredListText(NO_FILTER));
    }

    let emptyFiltered = true;
    const isStarred = (id) => favorites.includes(id);
    const gameList = games
        .map((g) => {
            g.time = Utils.getGameMinutes(g);
            return g;
        })
        .map((g) => {
            var extra = 0;
            if (g.state === 1 && g.time > 45) extra = g.time - 45
            else if (g.state === 3 && g.time > 90) extra = g.time - 90;
            var m = g.time;
            if (g.state === 1 && g.time > 45) m = 45
            else if (g.state === 2) m = 'HT'
            else if (g.state === 3 && g.time > 90) m = 90
            g.time = m;
            g.extra = extra;
            return g;
        }) 
        .map((g) => {
            let ok;
            switch(filter) {
                case FILTER_15MIN : ok = GameUtils.isHalfEnding(g); break;
                case FILTER_HALFTIME : ok = g.state === 2; break;
                case FILTER_PREDICTABLE : ok = GameUtils.isPredictable(g); break;
                case FILTER_FAVORITE : ok = false; break;
                default : ok = true;
            }
            ok |= isStarred(g.id);
            g.hide = !ok;
            emptyFiltered &= g.hide;
            return g;
        }) 
        .sort((a, b) => { 
            let s;
            const compareStarred = (a, b) => +isStarred(b.id) - +isStarred(a.id);
            const compareState = (a, b) => b.state - a.state;
            const compareTime = (a, b) => b.time - a.time;
            const compareLeague = (a, b) => a.league === b.league ? 0 : (a.league > b.league ? 1 : -1);
            const compareId = (a, b) => a.id - b.id;
            const compareShots = (a, b) => GameUtils.getHalfMaxShots(b) - GameUtils.getHalfMaxShots(a);
            const compareShotsOnGoal = (a, b) => GameUtils.getHalfMaxShotsOnGoal(b) - GameUtils.getHalfMaxShotsOnGoal(a);

            if ((s = compareStarred(a, b))) return s;
            switch(sort) {
                case SORT_TIME: 
                    if ((s = compareState(a, b))) return s;
                    if ((s = compareTime(a, b))) return s;
                    if ((s = compareLeague(a, b))) return s;
                    break;
                case SORT_SHOTS: 
                    if ((s = compareShots(a, b))) return s;
                    if ((s = compareShotsOnGoal(a, b))) return s;
                    if ((s = compareState(a, b))) return s;
                    if ((s = compareTime(a, b))) return s;
                    if ((s = compareLeague(a, b))) return s;
                    break;
                case SORT_LEAGUE: 
                    if ((s = compareLeague(a, b))) return s;
                    if ((s = compareState(a, b))) return s;
                    if ((s = compareTime(a, b))) return s;
                    break;
                default : break;
            }
            return compareId(a, b);
        })
        .map((g) => {
            const hidden = g.hide ? ' hidden' : '';
            return (
                <li className={'item-list list-group-item' + hidden} key={g.id}>
                    <Game game={g} starred={favorites.includes(g.id)} setFavorites={setFavorites}/>
                </li>
            );
        });

    if (emptyFiltered) {
        return emptyListView(getEmptyFilteredListText(filter));
    }
    return (
        <div className='app-games'>
            <ul className = 'item-list list-group'>
                {gameList}
                <li></li>
            </ul>
        </div>
    );
}

export default AppGames;