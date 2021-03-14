import  { Utils, GameUtils } from '../../utils';

import Game from '../game';
import { Loader } from '../blocks';

import './app_games.css';
import { FILTER_15MIN, FILTER_HALFTIME } from '../filter';
import { SORT_TIME, SORT_SHOTS, SORT_LEAGUE } from '../sort';

const AppGames = ({ games, filter, sort }) => {

    if (!games) {
        return <Loader/>;
    }
    if (!games.length) {
        return <div className="app-games no-game">No live game with statistics at the moment</div>
    }

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
        .filter((g) => {
            switch(filter) {
                case FILTER_15MIN : return (g.time >= 30 && g.state === 1) || (g.time >= 75 && g.state === 3);
                case FILTER_HALFTIME : return g.state === 2;
                default : return true;
            }
        }) 
        .sort((a, b) => { 
            let s;
            const compareState = (a, b) => b.state - a.state;
            const compareTime = (a, b) => b.time - a.time;
            const compareLeague = (a, b) => a.league === b.league ? 0 : (a.league > b.league ? 1 : -1);
            const compareId = (a, b) => a.id - b.id;
            const compareShots = (a, b) => GameUtils.getHalfMaxShots(b) - GameUtils.getHalfMaxShots(a);
            switch(sort) {
                case SORT_TIME: {
                    if ((s = compareState(a, b))) return s;
                    if ((s = compareTime(a, b))) return s;
                    if ((s = compareLeague(a, b))) return s;
                }
                case SORT_SHOTS: {
                    if ((s = compareShots(a, b))) return s;
                    if ((s = compareState(a, b))) return s;
                    if ((s = compareTime(a, b))) return s;
                    if ((s = compareLeague(a, b))) return s;
                }
                case SORT_LEAGUE: {
                    if ((s = compareLeague(a, b))) return s;
                    if ((s = compareState(a, b))) return s;
                    if ((s = compareTime(a, b))) return s;
                }
            }
            return compareId(a, b);
        })
        .map((game) => {
            return (
                <li className='item-list list-group-item' key={game.id}>
                    <Game game={game}/>
                </li>
            );
        });

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