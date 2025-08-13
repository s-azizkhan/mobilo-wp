export const getPropValue = (props: any) => {

    const { styling, id, target, style, extra } = props;

    if (extra) {
        if (
            typeof styling.styles !== 'undefined' &&
            typeof styling.styles[id] !== 'undefined' &&
            typeof styling.styles[id][target] !== 'undefined' &&
            typeof styling.styles[id][target][style] !== 'undefined' &&
            typeof styling.styles[id][target][style][extra] !== 'undefined'
        )
            return styling.styles[id][target][style][extra];
        else
            return null;
    } else {
        if (
            typeof styling.styles !== 'undefined' &&
            typeof styling.styles[id] !== 'undefined' &&
            typeof styling.styles[id][target] !== 'undefined' &&
            typeof styling.styles[id][target][style] !== 'undefined'
        )
            return styling.styles[id][target][style];
        else
            return null;
    }

}