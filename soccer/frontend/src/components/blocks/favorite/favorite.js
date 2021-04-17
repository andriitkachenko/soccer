import {Icon} from '@material-ui/core';
import classNames from 'classnames';

import './favorite.css';

const Favorite = ({starred, setFavorite}) => {
    const icon = starred ? 'star' : 'star_border';
    const iconClass = classNames('fav-icon', { active : starred}); 
    return (
        <div className='fav'>
            <Icon className={iconClass} fontSize='small' onClick={setFavorite} >{icon}</Icon>
        </div>
    )
}

export default Favorite;