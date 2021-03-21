import {Icon} from '@material-ui/core';
import './favorite.css';

const Favorite = ({starred, setFavorite}) => {
    const icon = starred ? 'star' : 'star_border';
    const iconClass = 'fav-icon' +  (starred ? ' active' : ''); 
    return (
        <div className='fav'>
            <Icon className={iconClass} fontSize='small' onClick={setFavorite} >{icon}</Icon>
        </div>
    )
}

export default Favorite;