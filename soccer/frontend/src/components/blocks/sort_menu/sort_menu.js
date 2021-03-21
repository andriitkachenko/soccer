import React from 'react';
import {List, ListItem, ListItemText, ListItemIcon} from '@material-ui/core'; 
import {Sort as SortIcon, Check} from '@material-ui/icons'; 
import PopupMenu from '../popup_menu';

import {SORT_TIME, SORT_SHOTS, SORT_LEAGUE} from '../../sort';

const SortMenu = ({curSort, setSort}) => {

    const makeBody = (onSelect) => {

        const changeSort = (sort) => () => {
            setSort(sort);
            onSelect();
        }    

        const makeListItem = (sort, title) => {
            const icon = sort !== curSort
                ? <ListItemIcon className='invisible'><Check/></ListItemIcon>
                : <ListItemIcon><Check/></ListItemIcon>
            return (
                <ListItem button onClick={changeSort(sort)}>
                    {icon}
                    <ListItemText primary={title} />
                </ListItem>
            );
        }

        return (
            <List component="nav">
                {makeListItem(SORT_TIME, 'Time')}
                {makeListItem(SORT_SHOTS, 'Shots')}
                {makeListItem(SORT_LEAGUE, 'League')}
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