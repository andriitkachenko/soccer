import './scores.css';

const Scores = ({host, guest, all, h1, h2}) => {
    const isAll = all || all === 0;
    const isHalf1 = h1 || h1 === 0;
    const isHalf2 = h2 || h2 === 0;

    const clsName = 'scores'
        + (host ? ' h' : '' )
        + (guest ? ' g' : '')
        + (isAll  ? ' all' : '')
        + (isHalf1 ? ' half1' : '' )
        + (isHalf2 ? ' half2' : '');

    const score = isAll ? all : (isHalf1 ? h1 : h2);
    return (
        <div className={ clsName }>{ score }</div>
    );
}

export default Scores;