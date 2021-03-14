import React from 'react';
import {useState} from 'react'; 
import {Popover, IconButton} from '@material-ui/core'; 

const PopupMenu = ({id, icon, makeBody}) => {
    const [anchor, setAnchor] = useState(null);
    const openMenu = (event) => {
        setAnchor(event.currentTarget);
    }
    const closeMenu = () => {
        setAnchor(null);
    }
    const open = Boolean(anchor);
    const _id = open ? id : undefined;
    const body = makeBody(closeMenu);
    return (
        <div>
            <IconButton 
                aria-describedby={_id} 
                edge="start"  
                color="inherit" 
                disableRipple={true} 
                onClick={openMenu}
            >
                {icon}
            </IconButton>
            <Popover
                id={_id}
                open={open}
                anchorEl={anchor}
                onClose={closeMenu}
                anchorOrigin={{
                    vertical: 'bottom',
                    horizontal: 'center',
                }}
                transformOrigin={{
                    vertical: 'top',
                    horizontal: 'center',
                }}                
            >   
                {body}
            </Popover>
        </div>
    );
}

export default PopupMenu;