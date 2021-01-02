import Utils from '../../utils';

import Game from '../game';
import { Loader } from '../blocks';

import './app_games.css';

const AppGames = ({games}) => {

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
        .sort((a, b) => { 
            var s = b.state - a.state; 
            if (s) return s;
            s = b.time - a.time;
            if (s) return s;
            s = a.league === b.league ? 0 : (a.league > b.league ? 1 : -1);
            if (s) return s;
            s = a.id - b.id;
            return s;
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