import {Icon} from '@material-ui/core';
import { useState } from 'react';
import './favourite.css';

const Favourite = () => {
    const [fav, setFav] = useState(false);
    const setFavourite = () => {
        const newFav = !fav;
        setFav(newFav);
    }
    const icon = fav ? 'star' : 'star_border';
    const iconClass = 'fav-icon' +  (fav ? ' active' : ''); 
    return (
        <div className='fav'>
            <Icon className={iconClass} fontSize='small' onClick={setFavourite} >{icon}</Icon>
        </div>
    )
}

export default Favourite;