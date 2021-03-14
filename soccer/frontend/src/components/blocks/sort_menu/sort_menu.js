import React from 'react';
import {List, ListItem, ListItemText} from '@material-ui/core'; 
import {Sort as SortIcon} from '@material-ui/icons'; 
import PopupMenu from '../popup_menu';

import {SORT_TIME, SORT_SHOTS, SORT_LEAGUE} from '../../sort';

function SortMenu({setSort}) {
    const setSortTime = (onSelect) => () => {
        setSort(SORT_TIME);
        onSelect();
        
    }
    const setSortShots = (onSelect) => () => {
        setSort(SORT_SHOTS);
        onSelect();
    }
    const setSortLeague = (onSelect) => () => {
        setSort(SORT_LEAGUE);
        onSelect();
    }
    const makeBody = (onSelect) => {
        return (
            <List component="nav">
                <ListItem button onClick={setSortTime(onSelect)}>
                    <ListItemText primary="Time" />
                </ListItem>
                <ListItem button onClick={setSortShots(onSelect)}>
                    <ListItemText primary="Shots" />
                </ListItem>
                <ListItem button onClick={setSortLeague(onSelect)}>
                    <ListItemText primary="League" />
                </ListItem>
            </List>
        );
    }
    return (
        <PopupMenu 
            id='sort_menu' 
            icon={<SortIcon/>} 
            makeBody = {makeBody}
        />
    )
}

export default SortMenu;