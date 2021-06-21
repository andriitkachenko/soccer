import { connect } from 'react-redux';

import { setFavoritesAction } from '../../../actions';

import {Icon} from '@material-ui/core';
import classNames from 'classnames';

import './favorite.css';

const Favorite = ({gameId, starred, setFavorite}) => {
    const icon = starred ? 'star' : 'star_border';
    const iconClass = classNames('fav-icon', { active : starred}); 
    return (
        <div className='fav'>
            <Icon className={iconClass} fontSize='small' onClick={() => setFavorite(gameId)} >{icon}</Icon>
        </div>
    )
}

const state2props = ({ favorites }, { gameId }) => {
    return { 
        starred : favorites.includes(gameId),
    };
}

const dispatch2props = (dispatch) => {
    return {
        setFavorite : (gameId) => dispatch(setFavoritesAction(gameId)),
    }
}

export default connect(state2props, dispatch2props)(Favorite);