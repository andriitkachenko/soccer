import React from 'react';
import {List, ListItem, ListItemText, ListItemIcon} from '@material-ui/core'; 
import {FilterList, Check} from '@material-ui/icons'; 
import PopupMenu from '../popup_menu';

import { NO_FILTER, FILTER_FAVORITE, FILTER_15MIN, FILTER_HALFTIME, FILTER_ACTIVE, getFilterTitle } from '../../filter';


const FilterMenu = ({curFilter, setFilter}) => {
    const makeBody = (onSelected) => {
        const onFilterClick = (filter) => () => { 
            setFilter(filter); 
            onSelected();
        };
        const makeListItem = (filter) => {
            const icon = filter !== curFilter
                ? <ListItemIcon className='invisible'><Check/></ListItemIcon>
                : <ListItemIcon><Check/></ListItemIcon>;
            return (
                <ListItem button onClick={onFilterClick(filter)}>
                    {icon}
                    <ListItemText primary={getFilterTitle(filter)} />
                </ListItem>
            );
        }        
        return (
            <List component="nav">
                {makeListItem(NO_FILTER)}
                {makeListItem(FILTER_FAVORITE)}
                {makeListItem(FILTER_ACTIVE)}
                {makeListItem(FILTER_15MIN)}
                {makeListItem(FILTER_HALFTIME)}
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