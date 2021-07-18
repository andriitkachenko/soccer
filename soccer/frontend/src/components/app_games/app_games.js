import { connect } from 'react-redux';
import FlipMove from 'react-flip-move';


import Game from '../game';
import { Loader } from '../blocks';
import { NO_FILTER, getEmptyFilteredListText } from '../filter';
import  { devtrace } from '../../utils';

import './app_games.css';

const AppGames = ({ games, filter }) => {
    devtrace('AppGames');

    const emptyListView = (block) => {
        return <div className='app-games no-game'>{block}</div>;
    }
    if (!games) {
        return emptyListView(<Loader/>);
    }
    if (!games.length) {
        return emptyListView(getEmptyFilteredListText(NO_FILTER));
    }

    let allHidden = true;

    const gameList = games.map((g) => {
        const hidden = g.hide ? ' hidden' : '';
        allHidden &= g.hide;
        return (
            <li className={'item-list list-group-item' + hidden} key={g.id}>
                <Game game={g}/>
            </li>
        );
    });

    if (allHidden) {
        return emptyListView(getEmptyFilteredListText(filter));
    }
    return (
        <div className='app-games'>
            <ul className = 'item-list list-group'>
                <FlipMove duration={1000} easing="ease">
                    {gameList}
                </FlipMove>
            </ul>
        </div>
    );
}

const state2props = ({ games, filter }) => {
    return { games, filter };
}

export default connect(state2props)(AppGames);