import { useRef } from 'react';

import './scores.css';

const Scores = ({host, guest, all, h1, h2}) => {
    const prevScore = useRef(null);

    const isAll = all || all === 0;
    const isHalf1 = h1 || h1 === 0;
    const isHalf2 = h2 || h2 === 0;

    const score = isAll ? all : (isHalf1 ? h1 : h2);

    const changed = (prevScore.current < score);
    prevScore.current = score;

    const clsName = 'scores'
        + (host ? ' h' : '' )
        + (guest ? ' g' : '')
        + (isAll  ? ' all' : '')
        + (isHalf1 ? ' half1' : '' )
        + (isHalf2 ? ' half2' : '')
        + (changed ? ' inc' : '');

    return (
        <div className={ clsName }>{ score }</div>
    );
}

export default Scores;