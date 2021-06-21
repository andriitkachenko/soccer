import React from 'react';

import './league.css';

class League extends React.Component {
    render() {
        return (
            <span className='league'>{this.props.title}</span>
        )
    }
}

export default League;