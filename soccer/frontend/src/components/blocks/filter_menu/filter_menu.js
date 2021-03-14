import React from 'react';
import {List, ListItem, ListItemText} from '@material-ui/core'; 
import {FilterList} from '@material-ui/icons'; 
import PopupMenu from '../popup_menu';

import { NO_FILTER, FILTER_FAVORITE, FILTER_15MIN, FILTER_HALFTIME } from '../../filter';


const FilterMenu = ({setFilter}) => {
    const setNoFilter = (onSelected) => () => { 
        setFilter(NO_FILTER); 
        onSelected();
    };
    const setFilterFavorite = (onSelected) => () => { 
        setFilter(FILTER_FAVORITE); 
        onSelected();
    };
    const setFilter15min = (onSelected) => () => {
        setFilter(FILTER_15MIN);
        onSelected();
    };
    const setFilterHalftime = (onSelected) => () => {
        setFilter(FILTER_HALFTIME);
        onSelected();
    };
    const makeBody = (onSelected) => {
        return (
            <List component="nav">
                <ListItem button onClick={setNoFilter(onSelected)}>
                    <ListItemText primary="All" />
                </ListItem>
                <ListItem button onClick={setFilterFavorite(onSelected)}>
                    <ListItemText primary="Favorite" />
                </ListItem>
                <ListItem button onClick={setFilter15min(onSelected)}>
                    <ListItemText primary="Last 15 min" />
                </ListItem>
                <ListItem button onClick={setFilterHalftime(onSelected)}>
                    <ListItemText primary="Half time" />
                </ListItem>
            </List>
        );
    }
    return (
        <PopupMenu 
            id='filter_menu' 
            icon={<FilterList/>} 
            makeBody={makeBody}
        />
    )
}

export default FilterMenu;