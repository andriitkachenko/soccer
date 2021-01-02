import React, { useRef} from 'react';

import './shots.css';

const Shots = ({host, guest, half1, half2, last10, state, sg, sh}) => {
    const prevSh = useRef(null);
    const prevSg = useRef(null);
    
    const show = sg || sh || sg === 0 || sh === 0;

    const changed = (prevSg.current && prevSg.current < sg) || (prevSh.current && prevSh.current < sh);
    prevSg.current = sg;
    prevSh.current = sh;
    
    const clsName = 'shots' 
        + (host ? ' h' : '') 
        + (guest ? ' g' : '') 
        + (half1 ? ' half1' : '') 
        + (half2 ? ' half2' : '') 
        + (last10 ? ' last10' : '')
        + ((half1 || half2) && changed ? ' inc' : '')
        + ((state === 1 && half1) || (state === 3 && half2) ? ' bold' : '');

    return (
        <div  className= {clsName}>{ show ? `${sh ? sh : 0}-${sg ? sg : 0}` : '' }</div>
    );

}

export default Shots;