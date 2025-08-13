import React, { useEffect, useState } from 'react';
import { useLocation } from 'react-router-dom';
import { List, Typography } from 'antd';

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

import { orderFormActions } from '../../../store/actions';

const { fetchOrderForm } = orderFormActions;

const Locations = (props: any) => {

    const { actions } = props;
    const { fetchOrderForm } = actions;

    const [locations, setLocations] = useState([]);

    const params = useLocation().search;
    const urlParams = new URLSearchParams(params);
    const postID = urlParams.get("post") || 0;

    useEffect(() => {
        if (postID > 0) {
            fetchOrderForm({
                id: postID,
                successCB: (data: any) => {
                    setLocations(data.locations)
                },
                failCB: () => {
                    console.log('error')
                }
            });
        }
    }, [postID])

    return (
        <List
            dataSource={locations}
            renderItem={(item: any) => {
                return (
                    <List.Item>
                        <a href={item.permalink} target="_blank">{item.post_title}</a>
                    </List.Item>
                )
            }}
        />
    )

}


const mapStateToProps = (store: any, props: any) => ({
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        fetchOrderForm
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(Locations)
