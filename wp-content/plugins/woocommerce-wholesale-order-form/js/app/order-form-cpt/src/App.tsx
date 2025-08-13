import React from 'react';
import OrderFormList from './components/OrderFormList';
import Editor from './components/Editor';
import DisplayMinReqNotice from './DisplayMinReqNotice';

import 'antd/dist/antd.css';
import { Button, PageHeader } from 'antd';
import {
  useLocation,
  Link
} from "react-router-dom";

const App = () => {

  const pathName = useLocation().pathname;
  const params = useLocation().search;
  const urlParams = new URLSearchParams(params);

  const page = urlParams.get("sub-page");
  const postID = urlParams.get("post") || 0;

  const RenderComponent = () => {

    if (page === 'add-new' || (page === 'edit' && postID > 0))
      return <Editor />;
    else {
      const siteURL = params.length > 0 ? `${pathName}${params}&` : `${pathName}?`;
      return (
        <>
          <DisplayMinReqNotice />
          <PageHeader
            className="page-header"
            title="Order Forms"
            subTitle={
              <Link to={`${siteURL}sub-page=add-new`}>
                <Button style={{ background: '#f3f5f6', color: "#0071a1", border: "1px solid #0071a1", fontWeight: "bold" }}>Add Form</Button>
              </Link>}
          />

          <p style={{ margin: '20px 0px' }}>
            Below is a list of all the order forms for displaying products that you have on your store. Click each form to edit it's characteristics or add a new form using the button above.
            Forms can be placed on pages via the given shortcode or via an editor block. Forms can be reused on multiple pages and you can also query what pages each form appears on.
          </p>
          <OrderFormList />
        </>
      );
    }

  }

  return (
    <div className="order-form-cpt">
      <RenderComponent />
    </div>
  );

}

export default App;